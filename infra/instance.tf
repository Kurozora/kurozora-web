resource "aws_instance" "search" {
  ami                    = data.aws_ami.al2023_arm64.id
  instance_type          = var.instance_type
  key_name               = var.ssh_key_name
  vpc_security_group_ids = [aws_security_group.search.id]
  iam_instance_profile   = aws_iam_instance_profile.search.name
  ebs_optimized          = true
  monitoring             = false

  metadata_options {
    http_tokens                 = "required"
    http_endpoint               = "enabled"
    http_put_response_hop_limit = 2
    instance_metadata_tags      = "disabled"
  }

  root_block_device {
    volume_type           = "gp3"
    volume_size           = var.root_volume_size_gb
    iops                  = 3000
    throughput            = 125
    encrypted             = true
    delete_on_termination = true

    tags = {
      Name = "${local.name_prefix}-root"
    }
  }

  user_data_replace_on_change = true

  user_data = templatefile("${path.module}/user-data/bootstrap.sh.tftpl", {
    region                  = var.region
    domain_name             = var.domain_name
    certbot_email           = var.certbot_email
    meilisearch_version     = var.meilisearch_version
    meilisearch_env         = var.meilisearch_env
    redis_maxmemory         = var.redis_maxmemory
    swap_size_mb            = var.swap_size_mb
    ssm_meili_key_name      = local.ssm_meili_key_name
    ssm_redis_password_name = local.ssm_redis_password_name
  })

  tags = {
    Name = "${local.name_prefix}"
  }

  lifecycle {
    # Ignore AMI drift. `bootstrap` pulls everything from packages anyway,
    # so a TF-driven AMI bump shouldn't recreate the instance silently.
    ignore_changes = [ami]
  }
}
