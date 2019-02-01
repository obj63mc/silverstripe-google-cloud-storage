# Example ACLS for your Bucket

1. Generic ACL for full bucket - your accounts and your google service account key will have full access.
`gsutil get acl gs://[BUCKET_NAME]`

		[
			{
				"entity": "project-owners-XXXX",
				"projectTeam": {
				"projectNumber": "XXXX",
				"team": "owners"
			},
				"role": "OWNER"
			},
			{
				"entity": "project-editors-XXXX",
				"projectTeam": {
				"projectNumber": "XXXX",
				"team": "editors"
			},
				"role": "OWNER"
			},
			{
				"entity": "project-viewers-XXXX",
				"projectTeam": {
				"projectNumber": "XXXX",
				"team": "viewers"
			},
				"role": "READER"
			},
			{
				"email": "gcloud-moosylvania@appspot.gserviceaccount.com",
			    "entity": "user-gcloud-moosylvania@appspot.gserviceaccount.com",
			    "role": "OWNER"
			}
		]

2. On your 'public' directory - your acl set by -
`gsutil acl ch -r -u AllUsers:R gs://[BUCKET_NAME]/public`

		[
			{
				"entity": "project-owners-XXXX",
				"projectTeam": {
				"projectNumber": "XXXX",
				"team": "owners"
			},
				"role": "OWNER"
			},
			{
				"entity": "project-editors-XXXX",
				"projectTeam": {
				"projectNumber": "XXXX",
				"team": "editors"
			},
				"role": "OWNER"
			},
			{
				"entity": "project-viewers-XXXX",
				"projectTeam": {
				"projectNumber": "XXXX",
				"team": "viewers"
			},
				"role": "READER"
			},
			{
				"entity": "allUsers",
				"role": "READER"
			},
			{
				"email": "gcloud-moosylvania@appspot.gserviceaccount.com",
				"entity": "user-gcloud-moosylvania@appspot.gserviceaccount.com",
				"role": "OWNER"
			}
		]
