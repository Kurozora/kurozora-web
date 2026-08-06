resource "aws_security_group" "search" {
  name        = "${local.name_prefix}-sg"
  description = "Kurozora search host. nginx (80/443 TCP, 443 UDP), Redis (6379 from ECS), SSH."
  vpc_id      = data.aws_vpc.default.id

  tags = {
    Name = "${local.name_prefix}-sg"
  }

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_vpc_security_group_ingress_rule" "ssh" {
  for_each = toset(var.ssh_ingress_cidrs)

  security_group_id = aws_security_group.search.id
  cidr_ipv4         = each.value
  from_port         = 22
  to_port           = 22
  ip_protocol       = "tcp"
  description       = "SSH"
}

resource "aws_vpc_security_group_ingress_rule" "http_v4" {
  security_group_id = aws_security_group.search.id
  cidr_ipv4         = "0.0.0.0/0"
  from_port         = 80
  to_port           = 80
  ip_protocol       = "tcp"
  description       = "HTTP. LE challenge and HTTPS redirect"
}

resource "aws_vpc_security_group_ingress_rule" "http_v6" {
  security_group_id = aws_security_group.search.id
  cidr_ipv6         = "::/0"
  from_port         = 80
  to_port           = 80
  ip_protocol       = "tcp"
  description       = "HTTP. LE challenge and HTTPS redirect"
}

resource "aws_vpc_security_group_ingress_rule" "https_tcp_v4" {
  security_group_id = aws_security_group.search.id
  cidr_ipv4         = "0.0.0.0/0"
  from_port         = 443
  to_port           = 443
  ip_protocol       = "tcp"
  description       = "HTTPS over TCP (HTTP/1.1, HTTP/2)"
}

resource "aws_vpc_security_group_ingress_rule" "https_tcp_v6" {
  security_group_id = aws_security_group.search.id
  cidr_ipv6         = "::/0"
  from_port         = 443
  to_port           = 443
  ip_protocol       = "tcp"
  description       = "HTTPS over TCP (HTTP/1.1, HTTP/2)"
}

resource "aws_vpc_security_group_ingress_rule" "https_udp_v4" {
  security_group_id = aws_security_group.search.id
  cidr_ipv4         = "0.0.0.0/0"
  from_port         = 443
  to_port           = 443
  ip_protocol       = "udp"
  description       = "HTTPS over QUIC (HTTP/3)"
}

resource "aws_vpc_security_group_ingress_rule" "https_udp_v6" {
  security_group_id = aws_security_group.search.id
  cidr_ipv6         = "::/0"
  from_port         = 443
  to_port           = 443
  ip_protocol       = "udp"
  description       = "HTTPS over QUIC (HTTP/3)"
}

resource "aws_vpc_security_group_ingress_rule" "redis_from_ecs" {
  security_group_id            = aws_security_group.search.id
  referenced_security_group_id = var.ecs_security_group_id
  from_port                    = 6379
  to_port                      = 6379
  ip_protocol                  = "tcp"
  description                  = "Redis from ECS"
}

resource "aws_vpc_security_group_egress_rule" "all_v4" {
  security_group_id = aws_security_group.search.id
  cidr_ipv4         = "0.0.0.0/0"
  ip_protocol       = "-1"
  description       = "All outbound IPv4"
}

resource "aws_vpc_security_group_egress_rule" "all_v6" {
  security_group_id = aws_security_group.search.id
  cidr_ipv6         = "::/0"
  ip_protocol       = "-1"
  description       = "All outbound IPv6"
}

resource "aws_eip" "search" {
  domain = "vpc"

  tags = {
    Name = "${local.name_prefix}-eip"
  }
}

resource "aws_eip_association" "search" {
  instance_id   = aws_instance.search.id
  allocation_id = aws_eip.search.id
}
