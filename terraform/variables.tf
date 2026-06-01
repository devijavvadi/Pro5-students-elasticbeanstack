variable "aws_region" {
  type    = string
  default = "us-east-1"
}

variable "db_name" {
  type    = string
  default = "school_db"
}

variable "db_user" {
  type    = string
  default = "admin"
}

variable "db_password" {
  type        = string
  description = "Production Database Password"
  sensitive   = true # This hides the password from appearing in your CLI terminal logs
}
