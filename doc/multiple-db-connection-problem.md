Hi! I 've solved this by adding 'connection' and 'table' variables models specificing the database in wich the model is saved.

For example, i have a model called 'User' in the database called 'core_database' in the table users.
In the other side, i have a model called 'UserEvents' in the database called 'logs_database' in the table 'user_events'

So i will have a two conections on config/database.php file:
```
'core_db_connection' => [
        'driver' => 'mysql',
        'host' => host_ip,
        'port' => port,
        'database' => 'core_database',
        'username' => username,
        'password' => password,
        ....
    ],

'logs_db_connection' => [
        'driver' => 'mysql',
        'host' => host_ip,
        'port' => port,
        'database' => 'logs_database',
        'username' => username,
        'password' => password,
        ....
    ],
```
And the models will be like:
```
class User extends Authenticatable {
  protected $table = 'core_database.users';
  protected $connection = 'core_db_connection';
  ...
}

class UserEvents extends Model {
  protected $table = 'logs_database.user_events';
  protected $connection = 'logs_db_connection';
  ...
}
```
As @rs-sliske said, this was tested in databases in the same database server.
Database connections have the same host ip.
I have not tried with a different way

Using this configuration, i can make any query across two or more separeted database.
I hope this help you!
