# silverstripe-google-cloud-storage

SilverStripe module to store assets in Google Cloud Storage rather than on the local filesystem.

Note: This is a pre-release module, and does not currently implement any kind of
bucket policy for protected assets.

This is based primarily off of https://github.com/silverstripe/silverstripe-s3

## Environment setup

The module requires a few environment variables to be set. These are mandatory.

* `GC_PROJECT_ID`: Your google project id
* `GC_BUCKET_NAME`: The name of the cloud storage bucket bucket to store assets in.

If running outside of google app engine or cloud compute, you need to setup your user credentials by placing a service account key json file in the root of your site that has access to Google Cloud Storage JSON API. Then simply setup the environment variable of GOOGLE_APPLICATION_CREDENTIALS, example -

* `GOOGLE_APPLICATION_CREDENTIALS`: 'service-account.json'


## Installation

* Define the environment variables listed above.
* [Install Composer from https://getcomposer.org](https://getcomposer.org/download/)
* Run `composer require obj63mc/silverstripe-google-cloud-storage`

This will install the most recent applicable version of the module given your other Composer
requirements.

**Note:** This currently immediately replaces the built-in local asset store that comes with
SilverStripe with one based on Google Cloud Storage. Any files that had previously been uploaded to an existing
asset store will be unavailable (though they won't be lost - just run `composer remove obj63mc/silverstripe-google-cloud-storage` to remove the module and restore access).

## Configuration

Assets are classed as either 'public' or 'protected' by SilverStripe. Public assets can be
freely downloaded, whereas protected assets (e.g. assets not yet published) shouldn't be
directly accessed.

Currently the FlySystem adapter for google cloud storage does not implement Signed URLs.  
Due to Signed URLs not being implemented there is not a way to actually protect the unpublished assets.  If your site has protected files I would not recommend this module.

## Configuring Google Cloud Storage Bucket

It is recommended to set the bucket to be public so files can be accessed on your website.  To do so add a permission for the 'allUsers' user with
* Storage Object Viewer
* Storage Legacy Bucket Reader
* Storage Legacy Object Reader

If you are using a service account key json file, you also need to make sure that user has the following permissions -
* Storage Admin
* Storage Legacy Bucket Owner

Also make sure the service account key json user has access to the Cloud Storage JSON API - this can be enabled through 'APIs & Services >> Library' section of your Google Cloud Console.

It may also be useful to set all objects to be publicly readable when created - to do so you can run the following on  your command line -

		gsutil defacl ch -u AllUsers:R gs://{your bucket name}
