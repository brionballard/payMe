# Models
 - Balance
 - ApiKey
 - Transaction

# Events
- TransactionCreated
    - TransactionCreated will handle storing and logging transactions in both a database table and a log file stored in `storage/logs/transactions.log`. 
        - The purpose of the text file redundancy is to rebuild the activity table in the event of data loss or migration. While this may not be perfect in the sense of data retained in the log, for the purpose of this project it will only store pertinent information for the activity and balances i.e. it does not retain all user & card information like a live system might.

# API Endpoints
- Transactions `/api/transactions/{action}`
    - debit
    - charge
    - withdraw
    - deposit
    - refund

- Get Transactions - `/api/users/transactions`
- Get Balance - `/api/users/balance`
- Get ApiKeys - `/api/account/keys`

- Issue API Key - `/api/issue-key`
    - Returns a random user API key assuming your database has been migrated and seeded

# Step 0:
Update `env.example` to `.env` and add your local DB credentials.

# Step 1:
Migrate tables
For database driven queues
`php artisan migrate:fresh --seed && php artisan queue:table`

Or without db queues
`php artisan migrate:fresh --seed`

This will migrate the schema and generate all of the necessary data dependencies to run the application.

# Step 2: 
`php artisan test`

Tests will bypass the execution of Events with `Event::fake()`.

# Step 3: 
`php artisan serve`

Making a request to the `/api/transactions/{action}` endpoint will trigger a TransactionCreated event which should in turn store the transaction and queue the listener to write the data to the `storage/logs/transactions.log` file.

# Step 4(optional)
This is only necessary if you are using database driven queues
`php artisan queue:listen`

# Making Requests
All request should be made with a `X-API-KEY`. This is just to avoid true authentication for the purpose of this example. Validation of API keys can be found in `app/Http/Middleware/ValidApiKey.php`.

If you need to retrieve an API token or card ID, you may make a GET request to the following endpoint:
`/api/issue-key`. You will need the provided key to make request to the `/api/users/` and the `/api/transactions` endpoints.

If you would like to setup a postman collection to test the application, you can import the `API Routes.postman_collection.json` to PostMan.

### Queues
If your env `QUEUE_CONNECTION` is set to `sync`, the activity log will be logged by default. However, if it is set to database, you will need to ensure that you 

1) Have a jobs table available in your DB, if not, you can run `php artisan queue:table` 
2) The queue is being listened to while the server is running. To this you can run `php artisan queue:listen`



