output "function_arn" {
  description = "CloudFront Function ARN."
  value       = aws_cloudfront_function.hotlink_protection.arn
}

output "function_stage" {
  description = "CloudFront Function stage."
  value       = aws_cloudfront_function.hotlink_protection.stage
}
