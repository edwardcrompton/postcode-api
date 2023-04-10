# Symfony Postcode API

A postcode API based on data from Code-Point Open, an open dataset of all the
current postcode units in Great Britain.

The application contains:
- A console command to download and import UK postcodes into a database.
- An API endpoint to return partial string matches of postcodes as a JSON API
response.
- An API endpoint to return postcodes near a location as a JSON API response.

## Set up

The local development environment I use is Lando, an abstraction layer on top of
Docker Compose. First, [install Lando locally](https://docs.lando.dev/getting-started/installation.html).

Then, from the root of the codebase, start Lando:

```
> lando start
```

## Importing postcodes

The following command will download postcode data and import it into the
database:

```
lando console app:import-postcodes
```

Once postcodes are imported, you can start making API requests.

## Search for a partial postcode match

The following curl command will return postcodes containing 'SY208':

```
curl -X 'GET' \
  'https://passenger.lndo.site/api/v1/postcodes?postcode=SY208' \
  -H 'accept: application/json'
```

## Search for a postcode based on location

The following curl command will return postcodes within 500 meters of a location
specified in eastings and northings:

```
curl -X 'GET' \
  'https://passenger.lndo.site/api/v1/postcodes?location=274471,301316' \
  -H 'accept: application/json'
```

## Further development

- Convert from eastings and northings to lat, lon in order to allow postcodes to
be searched with these coordinates. proj4php\Proj4php could be used to do this.
A service will need to be injected into the `LocationFilter` class. The
`LocationFilter::filterProperty` method would then call $proj4php->transform in
order to convert lat, lon parameters into eastings and northings. Eastings and
northings would then be used in the filter in the same way they are now.

- Make the distance search configurable. We could include an additional
parameter in the API request to specify over what radius to return postcodes
that are close in location. Currently it's set to 500m.

- The location match returns postcode areas within a square, whose centre is
the specified location. This could be improved mathematically to search within
the radius of a circle, which would be a more accurate measure of distance.

- The process of importing postcodes take sometime. It would be good to be able
to specify a subset of postcodes to import to the database.

- Override existing postcodes in the database when importing, instead of purging
and starting from scratch.

- Provide a Docker Compose or plain Docker based environment which would provide
a quicker set up experience and could be adapted for a production environment.

- Add documentation generators in LocationFilter::getDescription().
