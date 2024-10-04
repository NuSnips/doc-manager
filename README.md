## Document Management App

This outlines the architecture and the design of this sample Document Management application.

### Concepts

1. Bounded context:

   - Document Management
   - DocumentShare Management
   - User Management

2. Ubiquitous language

   - Document: A file that's uploaded to the disk.
   - DocumentShare: A url that can be used to download a document.
   - User: An authenticated user that can upload/delete and view documents created by them.

### Application Layers

1. Domain Layer

   Contains the core business logic.

   - Entities (Objects that have a unique identity)

     - Document (id, name, path, user,documentShares, metadata, created_at, updated_at)
     - Metadata (id, document, size, type, tags, created_at, updated_at)
     - DocumentShare (id, documentShareDownload, url, expires_at,status, created_at, updated_at)
     - DocumentShareDownload (id, documentShare, count, created_at, updated_at)
     - User (id, first_name, last_name, email, password_hash,documents,created_at, updated_at)

   - Value Objects

     - Token: Holds the JWT token.

   - Repositories

     - DocumentRepository
     - DocumentShareRepository
     - UserRepository
     - ElasticSearchDocumentRepository

   - Services
     - DocumentService
     - UserService
     - AuthenticationService

2. Infrastructure Layer

   Manages persistence, security and external 3rd party services.

   - Authentication middleware
   - Implementation of Authentication service
   - Implementation of Domain repositories
   - ElasticSearch Repository implemetation.
   - Document storage logic

3. Application Layer

   Coordinates use cases and passes data to relevant layers.

   - Implementation of Domain services.
   - Commands to modify state.

4. Presentation Layer

Contains the Controllers that manages incoming request, validation, and response formatting.

### API endpoints

1.  Register a user
    - Endpoint `POST /register`
    - Description: Registers a user
    - Request body:
      ```
      {
          "first_name":"Jane",
          "last_name":"Smith",
          "email":"jane@email.com",
          "password":"password",
      }
      ```
2.  Login

    - Endpoint `POST /login`
    - Description: Login a user
    - Request body:

      ```
      {
          "email":"jane@email.com",
          "password":"password",
      }
      ```

    - Response:

      ```
        {
            "token": "eyJhbGciOiJIUzI1NiIsInR..."
        }
      ```

3.  Create a document

    - Endpoint `POST /documents`
    - Description: Upload a document
    - Headers: `Authorization: Bearer <JWT_TOKEN>`
    - Request body:

      - Form Data:
        - `document` : the file to upload
        - `tags`: array of tags

4.  List all documents created by authenticated user.

    - Endpoint `GET /documents`
    - Description: Retrieve all documents as an array.
    - Headers: `Authorization: Bearer <JWT_TOKEN>`
    - Response body:

    ```
    {
        'documents':[]
    }
    ```

5.  Search for documents created by authenticated user.

    - Endpoint `GET /documents?q=[searchTerm]`
    - Description: Retrieve all documents with name that matches the [searchTerm] as an array.
    - Headers: `Authorization: Bearer <JWT_TOKEN>`
    - Response body:

    ```
    {
        'documents':[]
    }
    ```

6.  List a single document created by authenticated user.

    - Endpoint `GET /documents/[id]`
    - Description: Retrieve a single document.
    - Headers: `Authorization: Bearer <JWT_TOKEN>`
    - Resonse body:

    ```
    {
        'document': Object
    }
    ```

7.  Deletes a document created by authenticated user.

    - Endpoint `DELETE /documents/[id]`
    - Description: Deletes document. - Headers: `Authorization: Bearer <JWT_TOKEN>`
    - Response body:

    ```
    {
        "success": true,
        "message": "Document deleted successfully."
    }
    ```

8.  Deletes a document created by authenticated user.

    - Endpoint `GET /documents/generate-url/[id]`
    - Description: Generate a shareable url
    - Headers: `Authorization: Bearer <JWT_TOKEN>`
    - Response body:

    ```
    {
        "success": true,
        "message": "Document shareable link generated successfully.",
        "link": "http://doc-manager.test/documents/download/af0c9ef6dce8fd34f17b538b62439865"
    }
    ```

### Instructions

Clone the repo

```
gh repo clone NuSnips/doc-manager
```

Install dependencies

```
composer install
```

Run migrations

```
php vendor/bin/doctrine orm:schema-tool:create
```
