provider "aws" {
  region = var.region

  allowed_account_ids = [var.aws_account_id]

  default_tags {
    tags = {
      Project     = "kurozora"
      ManagedBy   = "opentofu"
      Component   = "search"
      Environment = var.environment
    }
  }
}

locals {
  name_prefix = "kurozora-${var.environment}-search"

  ssm_meili_key_name      = "/kurozora/${var.environment}/search/meilisearch-master-key"
  ssm_redis_password_name = "/kurozora/${var.environment}/search/redis-password"
}
