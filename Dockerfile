FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# 1. Apache, PHP y herramientas (igual a las Actividades 1 y 6)
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    libapache2-mod-php \
    php-mysql \
    wget \
    tar \
    curl \
    nano \
    && rm -rf /var/lib/apt/lists/*

# 2. Habilitar mod_status (requisito de Apache Exporter)
RUN a2enmod status

# 3. Descargar Apache Exporter
RUN wget https://github.com/Lusitaniae/apache_exporter/releases/download/v1.0.8/apache_exporter-1.0.8.linux-amd64.tar.gz \
    && tar xvf apache_exporter-1.0.8.linux-amd64.tar.gz \
    && mv apache_exporter-1.0.8.linux-amd64/apache_exporter /usr/local/bin/ \
    && rm -rf apache_exporter*

# 4. Copiar TUS configuraciones reales (Actividades 3 y 4)
COPY apache-config/miapp.conf /etc/apache2/sites-available/miapp.conf
COPY apache-config/security.conf /etc/apache2/conf-available/security.conf
COPY apache-config/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 5. Crear el DocumentRoot de tu app
RUN mkdir -p /var/www/miapp

EXPOSE 80 9117

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
