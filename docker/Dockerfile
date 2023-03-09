# A partir de l'image docker officiel de PHP
FROM php
# Le r?pertoire de travail sera /app
# Le volume du container qui contient les fichiers sources de l'app Symfony
# sera ?galement ce r?pertoire
WORKDIR /app
# Je lance la commande Linux permettant de mettre ? jour les paquets
# Et d'installer le paquet wget
RUN  apt-get update && apt-get install -y wget libicu-dev \
     && docker-php-ext-install intl opcache pdo pdo_mysql \
     && rm -rf /var/lib/apt/lists/*
# Grace a wget, je r?cup?re le CLI de Symfony
RUN wget https://get.symfony.com/cli/installer -O - | bash
# Et je le d?place dans le r?pertoire /usr/local/bin pour pouvoir l'utiliser
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
# install pdo_mysql
#RUN
# enable pdo_mysql
#RUN docker-php-ext-enable pdo_mysql
# L'application Symfony sera accessible sur le port 8000
EXPOSE 8000
# installation du certificat pour le https
RUN symfony server:ca:install
# Je lance le symfony serve
CMD symfony serve:start