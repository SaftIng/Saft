# Saft.store.http

[READ-ONLY] _Saft.store.http_ subtree of the _Saft_ project.

## Local test environment

The following information, how to setup and run a local test environment with a SPARQL endpoint rely on that you use the following docker container: https://github.com/SaftIng/Saft-docker . If not, please adapt accordingly.

### About the test environment

The test environment is based on PHPUnit and expects a SPARQL endpoint, which allows read/write operations using the SPARQL interface without authentication.

### Use Virtuoso 6/7 as SPARQL endpoint

Start your Virtuoso server. Access the conductor and navigate to:

1. Database
2. Interactive SQL

Execute the following SQL queries in the shell:

```sql
GRANT EXECUTE ON DB.DBA.SPARQL_INSERT_DICT_CONTENT TO "SPARQL";
GRANT EXECUTE ON DB.DBA.SPARQL_DELETE_DICT_CONTENT TO "SPARQL";
GRANT EXECUTE ON SPARQL_DELETE_DICT_CONTENT to "SPARQL";
GRANT EXECUTE ON SPARQL_DELETE_DICT_CONTENT to SPARQL_UPDATE;
GRANT SPARQL_UPDATE to "SPARQL";
GRANT SPARQL_SPONGE to "SPARQL";
```
They give the `SPARQL` user the rights to run read and write SPARQL queries.

#### .. or use Virtuoso docker instead

You can use the Virtuoso docker container, provided by tenforce:

https://hub.docker.com/r/tenforce/virtuoso/

It allows you to simply set the environment variable `SPARQL_UPDATE` to `true`, which has the same effect as the SQL query above.
