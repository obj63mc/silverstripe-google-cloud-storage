# silverstripe-google-cloud-storage

SilverStripe module to store assets in Google Cloud Storage rather than on the local filesystem.

Note: This is a pre-release, and does not implement any kind of
bucket policy for protected assets.  There is an example ACL for public/protected assets [here](./acl-examples.md)

This was initially based off of https://github.com/silverstripe/silverstripe-s3

## Environment setup

The module requires a few environment variables to be set. These are mandatory.

* `GC_KEY_FILE`: JSON of your google service account json file
* `GC_BUCKET_NAME`: The name of the cloud storage bucket bucket to store assets in.


For the GC_KEY_FILE environment variable, simply copy all contents into one line and place in your .env file like -

        GOOGLE_KEY_FILE={"type": "service_account","project_id": ...

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

'public' assets are stored by default in a directory called 'public' in the root of your bucket.  If you would like to change this prefix/path simply update the environment variable of `GC_PUBLIC_BUCKET_PREFIX`.  You will want to configure this folder and any assets under it to have an ACL that anyone can read.

'protected' assets are stored by default in a directory called 'protected' in the root of your bucket.  If you would like to change this prefix/path simply update the environment variable of `GC_PROTECTED_BUCKET_PREFIX`.  You will want to configure this folder to be private and only your google project admins and the account service key have admin access.

## Configuring Google Cloud Storage Bucket

This is an example for setting up your Google Cloud Storage Bucket.  This assumes you have Google Cloud SDK and command line tools installed. https://cloud.google.com/storage/docs/quickstart-gsutil

1. Create the Bucket - `gsutil mb gs://[BUCKET_NAME]/`
2. Get the default ACL, you will need the project id from the ACL `gsutil acl get gs://[BUCKET_NAME]/`
3. Set the bucket so your service account key can access the bucket (note only needed if not running on google app engine or cloud compute).  Replace [YOUR_ACCOUNT_NAME]/the email address with your service account key email address.

        gsutil defacl ch -u gcloud-[YOUR_ACCOUNT_NAME]@appspot.gserviceaccount.com:OWNER gs://[BUCKET_NAME]
        gsutil acl ch -u gcloud-[YOUR_ACCOUNT_NAME]@appspot.gserviceaccount.com:OWNER gs://[BUCKET_NAME]
4. Lets create the 'public' folder and set it so anyone can read it.

        touch test.txt
        gsutil cp test.txt gs://[BUCKET_NAME]/public
        gsutil acl ch -r -u AllUsers:R gs://[BUCKET_NAME]/public

Your bucket should now be configured and have proper protected/public folders for your assets.

## Auto Generated Assets from the CMS (Tinymce.js/Error Pages)

### TinyMCE
Currently anytime you run a flush on your site and access the CMS, new versions of tinymce code and other JS may be outputted to your assets directory.  Instead of referencing these from your cloud storage, this module now includes an adapter to make sure this stays on the local filesystem as intended.

### ErrorPages - currently error pages static files will be uploaded to GCS.  Due to this you would need to update the path that these are generated to by editing your main sites config .yml file, default set from this project is:

        SilverStripe\ErrorPage\ErrorPage:
            store_filepath: 'error-pages'

You can also turn off the static file generation via -

        SilverStripe\ErrorPage\ErrorPage:
          enable_static_file: false
