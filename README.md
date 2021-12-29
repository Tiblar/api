## Running in Docker

Start the API server with: `docker-compose up --build`

# System Requirements
`apcu`
`imagick`
`sox`
`libsox-fmt-all`
`openssl`
`ffmpeg`

# PHP Requirements (=7.4)
`php-fpm`
`bcmath`
`curl`
`gd`
`json`
`libxml`
`openssl`
`mysql`

Increase maximum upload size to 100mb in `php.ini`.

Matrix config
```yaml
oidc_providers:
  - idp_id: formerly_chucks
    idp_name: formerly_chucks
    idp_brand: "org.matrix.formerly_chucks"
    discover: false
    skip_verification: true
    issuer: "http://localhost"
    client_id: "test-id" # TO BE FILLED
    client_secret: "secret" # TO BE FILLED
    authorization_endpoint: "http://localhost/login"
    token_endpoint: "http://localhost/api/v2/oauth/token"
    userinfo_endpoint: "http://localhost/api/v2/oauth/identity"
    scopes: ["identity"]
    user_mapping_provider:
      config:
        subject_claim: "data.user.id"
        localpart_template: "tb_{{ data.user.id}}"
        display_name_template: "{{ data.user.info.username }}"
```

Train spam filter:
```
php bin/console app:train-spam-filter --mode train
```

Cron
```
0 */24 * * * php /var/www/tiblar-api/bin/console app:calculate-storage
0 * * * * php /var/www/tiblar-api/bin/console app:calculate-analytics
0 * * * * php /var/www/tiblar-api/bin/console app:rate-spam-ips
```

Generate JWT keys:
```
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```