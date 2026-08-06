# CDN

OpenTofu config for the CloudFront Function in front of `cdn.kurozora.app`.

The S3 bucket and CloudFront distribution are referenced via `data` blocks. This module owns the hotlink protection function.

## Run

```bash
cp ../terraform.tfvars.example terraform.tfvars
$EDITOR terraform.tfvars

cd infra/cdn/
tofu init
tofu plan
tofu apply
```

## Attach the function to the distribution

```bash
DIST_ID="E16LK9WHLJO80X"
FN_ARN=$(tofu -chdir=infra/cdn output -raw function_arn)
aws cloudfront get-distribution-config --id "$DIST_ID" \
    --query 'DistributionConfig' --output json > /tmp/dist.json
ETAG=$(aws cloudfront get-distribution-config --id "$DIST_ID" \
    --query 'ETag' --output text)

jq --arg arn "$FN_ARN" '
  .DefaultCacheBehavior.FunctionAssociations = {
    Quantity: 1,
    Items: [{ FunctionARN: $arn, EventType: "viewer-request" }]
  }
' /tmp/dist.json > /tmp/dist-updated.json

aws cloudfront update-distribution --id "$DIST_ID" \
    --if-match "$ETAG" \
    --distribution-config file:///tmp/dist-updated.json
```

## Updating the JS

1. Edit `files/hotlink_protection.js`.
2. `tofu apply`.

## Removing hotlink protection

Strip the `FunctionAssociations` block from the distribution config, run `aws cloudfront update-distribution`, then `tofu destroy`.
