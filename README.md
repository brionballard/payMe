# Models
 - Card
 - Balance
 - ApiKey
 - ActivityLog

# Events
- AccountActivity
    - AccountActivity will handle storing and logging account activity in both a database table and a log file stored in `storage/logs/account_activity.log`. The purpose of the text file redundancy is to rebuild the activity table in the event of data loss or migration. While this may not be perfect in the sense of data retained in the log, for the purpose of this project it will only store pertinent information for the activity and balances i.e. it does not retain all user & card information like a live system might.

# Account Actions(HTTP API)
In a real application, these should be models with their own separate storage mechanism. 
- Charge
- Debit
- Withdraw (abstraction of charge)

# Step 1:
Migrate tables
`php artisan migrate --seed`

This will migrate the schema and generate all of the necessary data dependencies to run the application.

# Step 2: 
`php artisan test`

After executing the tests, you should be able to find a file `storage/logs/activity.log` to review the previous history of transactions.

### Queues
If your env `QUEUE_CONNECTION` is set to `sync`, the activity log will be logged by default. However, if it is set to database, you will need to ensure that you 

1) Have a jobs table available in your DB, if not, you can run `php artisan queue:table` 
2) The queue is being listened to while the server is running. To this you can run `php artisan queue:listen`


# Making Requests
All request should be made with a `X-API-KEY`. This is just to avoid true authentication for the purpose of this example. Validation of API keys can be found in `app/Http/Middleware/HasApiKey.php`.

If you need to retrieve an API token or card ID, you may make a GET request to the following endpoint:
`/api/deps`. You will need the provided information to make requests to the `/api/account/` endpoint.

## Dev Setup Commands
The following commands are all of the commands it will take to scaffold this project quickly from scratch.

`php artisan make:model Card -mf`
`php artisan make:model Balance -m`
`php artisan make:model ApiKey -mf`
`php artisan make:model ActivityLog -m`
`php artisan make:event AccountActivity`
`php artisan make:controller AccountController`
`php artisan make:event AccountActivity`
`php artisan make:listener AccountActivityListener --event=AccountActivity`
`php artisan make:test AccountTest`
`php artisan make:exception InvalidApiKey`
`php artisan make:exception InvalidAmountValue`
`php artisan make:exception NotSufficientFunds`
`php artisan make:middleware HasApiKey`

### Single command:
`php artisan make:model Card -mf && php artisan make:model Balance -m && php artisan make:model ApiKey -mf && php artisan make:model ActivityLog -m && php artisan make:event AccountActivity && php artisan make:controller AccountController && php artisan make:event AccountActivity && php artisan make:listener AccountActivityListener --event=AccountActivity && php artisan make:test AccountTest && php artisan make:exception InvalidApiKey && php artisan make:exception InvalidAmountValue && php artisan make:exception NotSufficientFunds && php artisan make:middleware HasApiKey`

#### Next step = code models & events

### Migrate
For database driven queues
`php artisan migrate:fresh --seed && php artisan queue:table`

Or without db queues
`php artisan migrate:fresh`


### Test 
`php artisan test`

### Serve
`php artisan serve`

### listen
`php artisan queue:listen`


