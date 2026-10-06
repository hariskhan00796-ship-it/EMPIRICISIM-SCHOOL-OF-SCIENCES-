# Empiricisim School of Sciences website

Static HTML pages and a Vercel serverless function for admission applications. The API validates submissions and stores them in a private Supabase table. The older `form.php` endpoint is not used by the deployed form.

## Configure Supabase

1. Create a Supabase project.
2. In its SQL Editor, run [`supabase/schema.sql`](supabase/schema.sql). Row-level security is enabled, and public roles have no table access.
3. In Supabase project settings, copy the project URL and the `service_role` secret key. Never use the secret key in browser code or commit it to GitHub.

## Deploy to Vercel

1. Push this project folder to a GitHub repository. Do not upload `.env` files, database files, or admissions data.
2. In Vercel, import the GitHub repository as a project. Select the `Other` framework preset and use this folder as the project root.
3. In the Vercel project settings, add these environment variables for Production and Preview:
	- `SUPABASE_URL`: the Supabase project URL.
	- `SUPABASE_SERVICE_ROLE_KEY`: the Supabase `service_role` secret key.
4. Deploy, then submit a test application and confirm that it appears in the Supabase table.

The application data contains personal information. Restrict access to the Supabase project to trusted school staff, use HTTPS, and never create a public page that lists applications. Before collecting real applications, test the form and decide who is responsible for managing, exporting, and deleting the records.

## Local development

Use Vercel CLI to run the site and API together. Set the two environment variables above in a local `.env.local` file, then run `vercel dev`. `.env.local` is ignored by Git and must not be committed.

## Image assets

The existing photos are retained. Increasing image detail requires higher-resolution source images.