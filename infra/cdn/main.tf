provider "aws" {
  region = var.region

  allowed_account_ids = [var.aws_account_id]

  default_tags {
    tags = {
      Project     = "kurozora"
      ManagedBy   = "opentofu"
      Component   = "cdn"
      Environment = var.environment
    }
  }
}

locals {
  name_prefix = "kurozora-${var.environment}-cdn"
}

data "aws_s3_bucket" "assets" {
  bucket = var.bucket_name
}

data "aws_cloudfront_distribution" "cdn" {
  id = var.distribution_id
}

resource "aws_cloudfront_function" "hotlink_protection" {
  name    = "${local.name_prefix}-hotlink-protection"
  runtime = "cloudfront-js-2.0"
  comment = "Hotlink protection for cdn.kurozora.app."
  publish = true
  code    = file("${path.module}/files/hotlink_protection.js")
}
