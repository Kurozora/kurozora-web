variable "aws_account_id" {
  description = "Account ID the provider must match."
  type        = string
}

variable "region" {
  description = "AWS region."
  type        = string
  default     = "eu-central-1"
}

variable "environment" {
  description = "Environment label used in resource names."
  type        = string
  default     = "production"
}

variable "bucket_name" {
  description = "S3 bucket fronted by CloudFront."
  type        = string
  default     = "kurozora-assets"
}

variable "distribution_id" {
  description = "CloudFront distribution ID serving cdn.kurozora.app."
  type        = string
  default     = "E16LK9WHLJO80X"
}

variable "alias" {
  description = "Public hostname routed to the distribution."
  type        = string
  default     = "cdn.kurozora.app"
}
