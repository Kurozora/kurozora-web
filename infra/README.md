# Search host

OpenTofu config for the EC2 box running Meilisearch and Redis.

Use this only to spin up a new server (disaster recovery or migration). No state is stored between runs.

## Contents

- `t4g.medium` on Amazon Linux 2023 (ARM64)
- 40 GB encrypted gp3 root
- EIP
- IAM role with SSM read and Session Manager
- Security group opens 22, 80/tcp, 443/tcp+udp, 6379/tcp from the ECS SG
- First-boot script installs:
  - Meilisearch
  - Redis 6
  - Nginx with HTTP/2 and HTTP/3
  - certbot
  - 2 GB swap
  - and a daily Meilisearch task pruner

Cloudflare DNS, Meilisearch indexes, and the ECS task definition are managed separately.

## Run

From GitHub:

1. Got to Actions -> Infra (search host)
2. Run workflow
3. Pick `plan` first
4. Pick `apply` and type `apply` in the confirmation field to provision

From a laptop:

```bash
cp terraform.tfvars.example terraform.tfvars
$EDITOR terraform.tfvars

cd infra/
tofu init
tofu plan
tofu apply
```

## After apply

1. Point Cloudflare DNS at the new EIP. The `public_ip` output has it.
2. Set proxy off so certbot's HTTP-01 challenge works.
3. SSH in and check the bootstrap log:

   ```bash
   ssh -i ~/.ssh/<ssh_key_name>.pem ec2-user@<public_ip> \
       'sudo tail -20 /var/log/kurozora-bootstrap.log'
   ```

   `==> Bootstrap complete` at the end means done. `Cert acquisition failed` means DNS wasn't ready. After DNS propagates, on the box:

   ```bash
   sudo certbot certonly --nginx \
       --domain <domain_name> --email <certbot_email> \
       --key-type ecdsa --agree-tos --non-interactive
   sudo /usr/local/sbin/install-tls-vhost <domain_name>
   ```
   
4. New `private_ip` and (if rotated) new `REDIS_PASSWORD` in the ECS task definition. Register a revision and force a new deployment.
5. Reseed Meilisearch:
6. 
   ```bash
   php artisan scout:import 'all'
   ```

## Operations

SSH:

```bash
ssh -i ~/.ssh/<ssh_key_name>.pem ec2-user@<public_ip>
```

If you don't have a key, you can use Session Manager:

```bash
aws ssm start-session --target <instance_id> --region eu-central-1
```

Rotate Redis password:

```bash
NEW=$(openssl rand -base64 32 | tr -d /+=)
aws ssm put-parameter --name /kurozora/production/search/redis-password \
    --type SecureString --value "$NEW" --overwrite --region eu-central-1

redis6-cli -a OLD_PASSWORD CONFIG SET requirepass "$NEW"
redis6-cli -a "$NEW" CONFIG REWRITE
```

Then update `REDIS_PASSWORD` in the ECS task def and force a new deployment.

Manually trigger the Meilisearch prune:

```bash
sudo systemctl start meilisearch-prune-tasks.service
```

## Retiring the old host

Tofu doesn't manage the old box, so terminate it from the AWS console:

1. EC2 Instances: terminate the old `Kurozora-search`.
2. EC2 Elastic IPs: release the old EIP if you don't need it.
3. EBS: delete the old root volume if it didn't auto-delete.
