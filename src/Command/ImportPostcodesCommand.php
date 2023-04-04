<?php

namespace App\Command;

use App\Entity\Postcode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Exception;
use ZipArchive;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

// the "name" and "description" arguments of AsCommand replace the
// static $defaultName and $defaultDescription properties
#[AsCommand(
    name: 'app:import-postcodes',
    description: 'Import postcodes from data files.',
    hidden: false,
    aliases: ['app:impc']
)]
class ImportPostcodesCommand extends Command
{
    /**
     * CSV file column indexes for imported data.
     */
    public const POSTCODE = 0;
    public const EASTINGS = 2;
    public const NORTHINGS = 3;

    /**
     * An artificial limit on the number of postcode files to import.
     *
     * Set to 0 to import all available files.
     */
    public const FILEIMPORTLIMIT = 0;

    /**
     * The source data URL to a zip file.
     *
     * @var string
     */
    protected $dataSourceUrl;

    /**
     * Client for fetching remote file data.
     *
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * Injected ZipArchive object.
     *
     * @var ZipArchive
     */
    protected $zipArchive;

    /**
     * Injected entity manager object.
     *
     * @var Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Path to the directory where postcode data files are kept.
     *
     * @var string
     */
    protected $dataPath = '/data/Data/CSV/';

    /**
     * String containing the root path of the application.
     *
     * @var string
     */
    protected $rootPath;

    /**
     * @param EntityManagerInterface $entityManager
     *  The entity manager used for handling object persistance.
     * @param string $rootPath
     *  The root path of the application.
     */
    public function __construct(
        ParameterBagInterface $params,
        HttpClientInterface $client,
        ZipArchive $zipArchive,
        EntityManagerInterface $entityManager,
        string $rootPath
    ) {
        $this->dataSourceUrl = $params->get('app.data_source_url');
        $this->client = $client;
        $this->zipArchive = $zipArchive;
        $this->entityManager = $entityManager;
        $this->rootPath = $rootPath;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Downloading data files</info>');
        try {
            $this->downloadDataFiles();
            $this->extractDataFiles();
        } catch (Exception $e) {
            return Command::FAILURE;
        }

        $output->writeln('<info>Purging existing postcodes from the database.</info>');
        $this->purgePostcodes();

        $output->writeln('<info>Importing postcodes from data files.</info>');
        $dataDirectoryPath = $this->rootPath . $this->dataPath;

        try {
            $this->readPostcodeFiles($dataDirectoryPath, $output);
        } catch (Error $e) {
            return Command::FAILURE;
        }

        $output->writeln('<info>Import complete.</info>');

        return Command::SUCCESS;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setHelp('Import postcodes from data files.');
    }

    /**
     * Download a zip file.
     */
    public function downloadDataFiles()
    {
        $response = $this->client->request('GET', $this->dataSourceUrl);

        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Cannot download source data files');
        }

        file_put_contents('/tmp/postcode_data.zip', $response->getContent());
    }

    /**
     * Extract data files from a zip.
     */
    public function extractDataFiles()
    {
        $this->zipArchive->open('/tmp/postcode_data.zip');
        $this->zipArchive->extractTo('data');
        $this->zipArchive->close();
    }

    /**
     * Reads postcode data from data files.
     *
     * @param string $dataDirectoryPath
     *  Path to the directory holding the data files.
     */
    public function readPostcodeFiles(string $dataDirectoryPath, OutputInterface $output)
    {
        $dirHandle = opendir($dataDirectoryPath);

        if (!$dirHandle) {
            return null;
        }

        // The number of files imported.
        $fileCount = 0;

        do {
            $fileName = readdir($dirHandle);
            // Skip '.' and '..'.
            if ($fileName == '.' or $fileName == '..') {
                continue;
            }

            $output->writeln('Importing file ' . $fileName . '.');

            if (($fileHandle = fopen($dataDirectoryPath . $fileName, "r")) !== false) {
                $rowCount = 0;
                while (($row = fgetcsv($fileHandle)) !== false) {
                    $this->createPostCode(
                        $row[self::POSTCODE],
                        $row[self::EASTINGS],
                        $row[self::NORTHINGS]
                    );
                    $rowCount++;
                }

                $output->writeln('File imported with ' . $rowCount . ' rows.');

                // Write all created entities to the database.
                $this->entityManager->flush();

                $fileCount++;
            }

            // Break out of the loop if we've reached the file import limit.
            if (self::FILEIMPORTLIMIT > 0 && $fileCount >= self::FILEIMPORTLIMIT) {
                break;
            }
        } while($fileName !== false);

        closedir($dirHandle);
    }

    /**
     * Persists a postcode to the database.
     *
     * @param string $code
     *  The full postcode.
     *
     * @param string $eastings
     *  The position eastings.
     *
     * @param string $northings
     *  The position northings.
     */
    public function createPostCode($code, $eastings, $northings, $overwrite = true): void
    {
        $postcode = new Postcode();
        $postcode->setPostcode($code);
        $postcode->setEastings($eastings);
        $postcode->setNorthings($northings);

        $this->entityManager->persist($postcode);
    }

    /**
     * Purge all existing postcodes in order to start again.
     */
    public function purgePostcodes()
    {
        $repository = $this->entityManager->getRepository(Postcode::class);
        $postcodes = $repository->findAll();

        foreach($postcodes as $postcode) {
            $this->entityManager->remove($postcode);
        }

        $this->entityManager->flush();
    }
}
