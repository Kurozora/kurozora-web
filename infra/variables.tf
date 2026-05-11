variable "aws_account_id" {
  description = "Account ID the provider must match. Set via TF_VAR_aws_account_id or terraform.tfvars."
  type        = string
}

variable "region" {
  description = "AWS region."
  type        = string
  default     = "eu-central-1"
}

variable "environment" {
  description = "Environment label used in resource names and SSM paths."
  type        = string
  default     = "production"
}

variable "instance_type" {
  description = "EC2 instance type. ARM64 only. Bootstrap installs aarch64 binaries."
  type        = string
  default     = "t4g.medium"
}

variable "ssh_key_name" {
  description = "Existing EC2 key pair name."
  type        = string
}

variable "root_volume_size_gb" {
  description = "Root EBS volume size, GiB."
  type        = number
  default     = 40
}

variable "swap_size_mb" {
  description = "Swapfile size, MiB."
  type        = number
  default     = 2048
}

variable "domain_name" {
  description = "FQDN served by nginx. Subject of the Let's Encrypt cert."
  type        = string
}

variable "certbot_email" {
  description = "Email for Let's Encrypt expiry warnings. Empty registers the cert without contact info."
  type        = string
  default     = ""
}

variable "ecs_security_group_id" {
  description = "Source security group for Redis (6379/tcp) ingress."
  type        = string
}

variable "ssh_ingress_cidrs" {
  description = "CIDR blocks allowed to SSH. Tighten in tfvars."
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "meilisearch_version" {
  description = "Meilisearch release tag, e.g. v1.19.0."
  type        = string
}

variable "meilisearch_env" {
  description = "Meilisearch --env mode."
  type        = string
  default     = "development"

  validation {
    condition     = contains(["development", "production"], var.meilisearch_env)
    error_message = "meilisearch_env must be 'development' or 'production'."
  }
}

variable "redis_maxmemory" {
  description = "Redis maxmemory ceiling. Paired with allkeys-lru eviction."
  type        = string
  default     = "256mb"
}
