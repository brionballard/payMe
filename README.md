# Models
 - Card
 - Balance
 - ApiKey
 - ActivityLog

# Events
- AccountActivity
    - AccountActivity will handle storing and logging account activity in both a database table and a log file stored in `storage/logs/account_activity.log`. 
        - The purpose of the text file redundancy is to rebuild the activity table in the event of data loss or migration. While this may not be perfect in the sense of data retained in the log, for the purpose of this project it will only store pertinent information for the activity and balances i.e. it does not retain all user & card information like a live system might.

# API Endpoints
- Charge - `/account/charge`
- Debit - `/account/debit`
- Withdraw (abstraction of charge) - `/account/withdraw`
- Get Activity - `/account/activity`
- Get Cards - `/account/cards`
- Get ApiKeys - `/account/keys`

- Get test data - `/api/deps`

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

After executing the tests, you should be able to find a file `storage/logs/activity.log` to review the previous history of transactions.

# Step 3: 
`php artisan serve`

After executing the tests, you should be able to find a file `storage/logs/activity.log` to review the previous history of transactions.

# Step 4(optional)
This is only necessary if you are using database driven queues
`php artisan queue:listen`

# Making Requests
All request should be made with a `X-API-KEY`. This is just to avoid true authentication for the purpose of this example. Validation of API keys can be found in `app/Http/Middleware/ValidApiKey.php`.

If you need to retrieve an API token or card ID, you may make a GET request to the following endpoint:
`/api/deps`. You will need the provided information to make requests to the `/api/account/` endpoints.

If you would like to setup a postman collection to test the application, you can import the `routes.postman_collection.json` to PostMan.

### Queues
If your env `QUEUE_CONNECTION` is set to `sync`, the activity log will be logged by default. However, if it is set to database, you will need to ensure that you 

1) Have a jobs table available in your DB, if not, you can run `php artisan queue:table` 
2) The queue is being listened to while the server is running. To this you can run `php artisan queue:listen`



