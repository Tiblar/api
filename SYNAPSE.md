# synapse-docs (v1.27.0)

## Install

### MacOS Development

- May require to add the flag `env LDFLAGS="-I/usr/local/opt/openssl/include -L/usr/local/opt/openssl/lib"` to `pip install -e ".[all,test]"` to install.

### Setup S3 (optional)

Run the command:
```
pip3 install git+https://github.com/matrix-org/synapse-s3-storage-provider.git
```

### Config

- Set `server_name`
- Set `public_baseurl`

```yaml
enable_set_displayname: false
enable_set_avatar_url: false
enable_3pid_changes: false

enable_registration: false

password_config:
   enabled: false

enable_group_creation: true

database:
  name: psycopg2
  args:
    user: synapse_user
    password: password
    database: synapse
    host: localhost
    cp_min: 5
    cp_max: 10

# seconds of inactivity after which TCP should send a keepalive message to the server
keepalives_idle: 10

# the number of seconds after which a TCP keepalive message that is not
# acknowledged by the server should be retransmitted
keepalives_interval: 10

# the number of TCP keepalives that can be lost before the client's connection
# to the server is considered dead
keepalives_count: 3

oidc_providers:
  - idp_id: tiblar
    idp_name: Tiblar
    idp_brand: "org.matrix.tiblar"  # optional: styling hint for clients
    discover: false
    issuer: "http://localhost/"
    client_id: "your-client-id" # TO BE FILLED
    client_secret: "your-client-secret" # TO BE FILLED
    skip_verification: true # True if using http
    authorization_endpoint: "http://localhost/api/v2/oauth/auth"
    token_endpoint: "http://localhost/api/v2/oauth/token"
    userinfo_endpoint: "http://localhost/api/v2/oauth/identity"
    scopes: ["read:user"]
    user_mapping_provider:
      config:
        subject_claim: "id"
        localpart_template: "tb_{{ user.id }}"
        display_name_template: "{{ user.info.username }}"
        email_template: "{{ user.email }}"
        
user_directory:
  enabled: true
  search_all_users: true

# Remove this if not using s3
media_storage_providers:
  - module: s3_storage_provider.S3StorageProviderBackend
    store_local: true
    store_remote: true
    store_synchronous: true
    config:
      bucket: $BUCKET
      endpoint_url: $ENDPOINT
      access_key_id: $ACCESSKEY
      secret_access_key: $SECRETKEY

max_upload_size: 100M

federation_domain_whitelist:
# make sure empty

allow_guest_access: false
```

### Migrate existing users (optional)

Run the following command:
```
php bin/console app:create-matrix-users > user_external_ids.sql
```

Import the SQL file into PostgreSQL
```
psql -u PSQL_USER -h HOST_IP -d main -f user_external_ids.sql
```

### Setup admin API

Run the following command:
```
php bin/console app:create-admin-matrix-user
```

Run the following SQL or the SQL provided by the command:
```
UPDATE `users` set admin = 1 where name = "@tb_00000000000000000000:localhost:8008";
```

### Setup OAuth application
Replace `MATRIX_AUTH_REDIRECT_URL` with your URL
```
php bin/console app:create-oauth-matrix-application MATRIX_AUTH_REDIRECT_URL
```