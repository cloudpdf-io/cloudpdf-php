# CloudPDF PHP

PHP wrapper for the [CloudPDF API](https://cloudpdf.io/developers/api-docs) - an cloud-based PDF management service. 

## Installation

Install the package with:

    composer require cloudpdf/api

To use the bindings, load it via Autoload

```php
require_once('vendor/autoload.php');
```

## Usage

### Table of Contents

- [Authentication](#authentication)
- [Account Info](#account-info)
- [Documents](#documents)
- [Webhooks](#webhooks)

### Authentication

Get the API key for your project from your CloudPDF Dashboard → Settings → API Keys.

```php
$config = array(
  'apiKey' => 'YOUR API KEY',
  'cloudName' => 'YOUR CLOUD NAME',
  'signingSecret' => 'YOUR SIGNING SECRET'
);

$cloudpdf = new CloudPDF\Client($config);
```

### Account Info

Return info about the Account associated with this API key.

```php
$cloudpdf->account()
```

### Documents

#### Create a Document

Before you can upload a PDF to CloudPDF you must create a new document.

The server will return a pre-signed Amazon upload URL where you can [upload your PDF file](#upload-file) using a PUT request.

After uploading the file you must [notify our server](#upload-file-completed) that the upload is finished and we will process the PDF by our PDF engine.

```php
$cloudpdf.createDocument([
  "name" => "your_document_name.pdf",
  "description" => "Description of your document",
  "tags" => ["tag1", "tag2"],
  "defaultPermissions" => [
    "download" => "Allowed",
    "search" => true,
    "selection" => false,
    "info" => ["email", "phone"]
  ]
])
```

##### Options

- `name`: The name of the PDF you want to upload (`string`)
- `description`: Description of the PDF you want to upload (`string`)
- `parentId`: The ID of the folder you want to create the document in (`string`)
- `tags`: Set tags on the document to easily filter and search documents later (`array`)
- `defaultPermissions`: Set the default permissions for this document. You can find all parameters in te [API docs](https://cloudpdf.io/developers/api-docs#create-document). If none are given we use the default permissions of the organization. You can change the default permissions of the organization in the Dashboard → Settings → Upload Settings. (`object`)

#### Get a Document

```php
$cloudpdf.getDocument("DOCUMENT ID")
```

#### Update a Document

```php
$cloudpdf.updateDocument("DOCUMENT ID", [
  "name" => "your_document_name.pdf",
  "description" => "Description of your document",
  "tags" => ["tag1", "tag2"],
  "defaultPermissions" => [
    "download" => "Allowed",
    "search" => true,
    "selection" => false,
    "info" => ["email", "phone"]
  ]
])
```

##### Options

- `name`: The name of the PDF you want to upload (`string`)
- `description`: Description of the PDF you want to upload (`string`)
- `parentId`: The ID of the folder you want to create the document in (`string`)
- `tags`: Set tags on the document to easily filter and search documents later (`array`)
- `defaultPermissions`: Set the default permissions for this document. You can find all parameters in te [API docs](https://cloudpdf.io/developers/api-docs#update-document). If none are given we use the default permissions of the organization. You can change the default permissions of the organization in the Dashboard → Settings → Upload Settings. (`object`)

#### Delete a Document

```php
$cloudpdf.deleteDocument("DOCUMENT ID")
```

#### Upload file

After you [create a document](#create-a-document) you will receive an Amazon signed URL where you can upload your PDF file using a PUT request. We suggest to upload the file directly from the clients browser to spare your server load. Below you find an example using axios in typescript.

When the file upload is finished you must [notify our server](#upload-file-completed).

```typescript
import axios from 'axios';

axios.put(signedUploadURL, file, {
  headers: {
    "Content-Type": "application/pdf"
  },
  onUploadProgress: (e) => {
    //  Show progress
    const percentComplete = Math.round((e.loaded * 100) / e.total);
    console.log(percentComplete);
  },
});
```

#### Upload file completed

After uploading your file to Amazon S3 you must notify our server on this endpoint that the upload is complete. On a successful request the status of the document will change from "WaitingUpload" to "Processing".

You can poll the [GET endpoint](#get-a-file) for status updates or [use a webhook](#webhooks) to find out if your document has completed processing by our PDF engine.

```php
$cloudpdf.uploadDocumentFileComplete("DOCUMENT ID", "FILE ID", ["uploadCompleted" => true])
```

#### Get a file

```php
$cloudpdf.getDocumentFile("DOCUMENT ID", "FILE ID")
```

### Webhooks

Webhooks are used to notify your system of specific CloudPDF events.

#### Create a Webhook

```php
$cloudpdf.createWebhook([
  "name" => "Webhook 1",
  "url" => "https://urltotrigger.com/",
  "events" => ["document.created"],
  "headers" => [
    "Authorization" => "Bearer secret"
  ]
])
```

##### Options

- `name`: The name of your webhook for your own reference (`string`)
- `url`: The URL that the webhook should trigger on a event (`string`)
- `secret`: Optional secret that you can use to secure the webhook endpoint (`string`)
- `events`: An array of events. Possible values are: document.created, document.updated, collection.created, collection.updated, tracker.new, lead.new, file.processed (`array`)
- `headers`: Object of header keys and values that will be sent as request header on the webhook request (`object`)

#### Get a Webhook

```php
$cloudpdf.getWebhook("WEBHOOK ID")
```

#### Update a Webhook

```php
$cloudpdf.updateWebhook("WEBHOOK ID", [
  "name" => "Webhook 1",
  "url" => "https://urltotrigger.com/",
  "events" => ["document.created"],
  "headers" => [
    "Authorization" => "Bearer secret"
  ]
])
```

##### Options

- `name`: The name of your webhook for your own reference (`string`)
- `url`: The URL that the webhook should trigger on a event (`string`)
- `secret`: Optional secret that you can use to secure the webhook endpoint (`string`)
- `events`: An array of events. Possible values are: document.created, document.updated, collection.created, collection.updated, tracker.new, lead.new, file.processed (`array`)
- `headers`: Object of header keys and values that will be sent as request header on the webhook request (`object`)

#### Delete a Webhook

```php
$cloudpdf.deleteWebhook("WEBHOOK ID")
```

#### List all Webhooks

```php
$cloudpdf.listWebhooks()
```

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/cloudpdf-io/cloudpdf-php.

## License

The repository is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT).
