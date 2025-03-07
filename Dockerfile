# Usa uma imagem base do PHP com Apache
FROM php:8.2-apache

# Define o diretório de trabalho dentro do container
WORKDIR /var/www/html

# Copia os arquivos do seu projeto para o diretório de trabalho
COPY . .

# Expõe a porta 80 (porta padrão do Apache)
EXPOSE 80

# Comando para iniciar o Apache
CMD ["apache2-foreground"]
