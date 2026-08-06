output "instance_id" {
  description = "EC2 instance ID."
  value       = aws_instance.search.id
}

output "public_ip" {
  description = "Elastic IP. Point Cloudflare DNS at this address."
  value       = aws_eip.search.public_ip
}

output "private_ip" {
  description = "Private IPv4. Use as REDIS_HOST in the ECS task definition."
  value       = aws_instance.search.private_ip
}

output "ssh_command" {
  description = "SSH command. Adjust the key path as needed."
  value       = "ssh -i ~/.ssh/${var.ssh_key_name}.pem ec2-user@${aws_eip.search.public_ip}"
}

output "ssm_parameter_names" {
  description = "SSM parameter paths for runtime secrets."
  value = {
    meilisearch_master_key = local.ssm_meili_key_name
    redis_password         = local.ssm_redis_password_name
  }
}

output "security_group_id" {
  description = "Security group attached to the instance."
  value       = aws_security_group.search.id
}
