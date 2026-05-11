import django.db.backends.mysql.base as mysql_base
mysql_base.DatabaseWrapper.mysql_version = (10, 6, 0)