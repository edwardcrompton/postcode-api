<?php

// src/Command/ImportPostcodes.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the "name" and "description" arguments of AsCommand replace the
// static $defaultName and $defaultDescription properties
#[AsCommand(
  name: 'app:import-postcodes',
  description: 'Import postcodes from the API.',
  hidden: false,
  aliases: ['app:impc']
)]
class ImportPostcodesCommand extends Command
{
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    echo "Importing postcodes from the API";
    // ... put here the code to create the user

    // this method must return an integer number with the "exit status code"
    // of the command. You can also use these constants to make code more readable

    // return this if there was no problem running the command
    // (it's equivalent to returning int(0))
    return Command::SUCCESS;

    // or return this if some error happened during the execution
    // (it's equivalent to returning int(1))
    // return Command::FAILURE;

    // or return this to indicate incorrect command usage; e.g. invalid options
    // or missing arguments (it's equivalent to returning int(2))
    // return Command::INVALID
  }

  protected function configure(): void
  {
    $this
      // the command help shown when running the command with the "--help" option
      ->setHelp('Import postcodes from the API.');
  }
}
