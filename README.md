# S3 CDN 

## Configuration

In your local configuration, specify something like the following to configure
the content Reader/Writer pair, along with actually binding them to usable 
content stores for the CDN 

    ---
    Name: locals3settings
    After: 
      - '#s3services'
    ---
	Injector:
	  S3Service:
	    constructor:
	      key: {your_api_key}
	      secret: {your_api_secret}
              region: {region}
	  S3ContentReader:
	    type: prototype
	    properties:
	      s3service: %$S3Service
	      bucket: {your_bucket_name}
	      baseUrl: https://{your_bucket_name}.s3.amazonaws.com
	  S3ContentWriter:
	    type: prototype
	    properties:
	      s3service: %$S3Service
	      bucket: {your_bucket_name}
	      baseUrl: https://{your_bucket_name}.s3.amazonaws.com
	  ContentService:
	    constructor:
	      defaultStore: Default
	    properties:
	      stores:
            Default:
              ContentReader: S3ContentReader
              ContentWriter: S3ContentWriter

Additionally, ensure you have the CDNFile extensions bound from the cdncontent
module

```yml

File:
  extensions:
    - CDNFile
Folder: 
  extensions:
    - CDNFolder

```


To change the default ACL applied on upload reconfigure the `defaultAcl` option

```
Injector
  S3ContentWriter:
    type: prototype
    properties:
      s3Service: %$S3Service
      bucket: bucket
      defaultAcl: ""
```

See the [CDN Content](https://github.com/symbiote/silverstripe-cdncontent) module
for more details

## Troubleshooting

### Resampled Images

The following queries may need to be run if you've done an SS upgrade and/or swapped to a new URL. This makes sure that all images are resampled correctly.

```
UPDATE File SET ResamplingsValue = '';
TRUNCATE TABLE `SilverStripeAustralia\ContentServiceAssets\ContentServiceAsset`;
TRUNCATE TABLE `Symbiote\ContentServiceAssets\ContentServiceAsset`;
```
