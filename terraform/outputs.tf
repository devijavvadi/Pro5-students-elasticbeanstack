output "elastic_beanstalk_app_name" {
  value = aws_elastic_beanstalk_application.app.name
}

output "elastic_beanstalk_env_name" {
  value = aws_elastic_beanstalk_environment.env.name
}

output "rds_connection_endpoint" {
  value = aws_db_instance.student_db.endpoint
}
