# Deployment Guide

Complete guide for deploying the Keycloak SSO Extension in various environments, including containerized deployments.

## Table of Contents

- [Package Installation Methods](#package-installation-methods)
- [Docker Deployment](#docker-deployment)
- [Kubernetes Deployment](#kubernetes-deployment)
- [Deployment Checklist](#deployment-checklist)
- [Environment-Specific Configuration](#environment-specific-configuration)
- [Troubleshooting Deployment](#troubleshooting-deployment)

## Package Installation Methods

### Method 1: Composer (Recommended)

The package is available via Composer and integrates seamlessly with any Krayin CRM installation.

```bash
# In your Krayin CRM root directory
composer require webkul/laravel-keycloak-sso

# Publish configuration and assets
php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider"

# Run migrations
php artisan migrate

# Clear cache
php artisan config:clear
php artisan cache:clear
```

**Benefits:**
- ✅ Automatic dependency resolution
- ✅ Laravel auto-discovery (service provider registered automatically)
- ✅ Easy updates via `composer update`
- ✅ Version locking with composer.lock
- ✅ Works in any environment (local, container, cloud)

### Method 2: Git Clone (Development)

For development or custom installations:

```bash
cd packages/Webkul
git clone https://github.com/kennis-ai/krayin-crm-keycloak.git KeycloakSSO
cd ../..

# Update composer.json autoload
composer dump-autoload

# Publish and migrate
php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider"
php artisan migrate
```

## Docker Deployment

### Option 1: Existing Krayin Container

If you already have a Krayin CRM Docker container:

**Step 1: Add to Dockerfile or docker-compose.yml**

```dockerfile
# In your Krayin Dockerfile
FROM webkul/krayin:latest

# Install Keycloak SSO Extension
RUN composer require webkul/laravel-keycloak-sso

# Copy environment configuration
COPY .env.keycloak /var/www/html/.env.keycloak

# Publish assets and run migrations during build (optional)
# RUN php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider"
# RUN php artisan migrate --force
```

**Step 2: docker-compose.yml Configuration**

```yaml
version: '3.8'

services:
  krayin:
    image: webkul/krayin:latest
    container_name: krayin-crm
    environment:
      # Laravel Configuration
      - APP_ENV=production
      - APP_DEBUG=false
      - APP_URL=https://crm.example.com

      # Database
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=krayin
      - DB_USERNAME=krayin
      - DB_PASSWORD=${DB_PASSWORD}

      # Keycloak SSO Configuration
      - KEYCLOAK_ENABLED=true
      - KEYCLOAK_BASE_URL=https://keycloak.example.com
      - KEYCLOAK_REALM=production
      - KEYCLOAK_CLIENT_ID=krayin-crm
      - KEYCLOAK_CLIENT_SECRET=${KEYCLOAK_CLIENT_SECRET}
      - KEYCLOAK_REDIRECT_URI=https://crm.example.com/admin/auth/keycloak/callback
      - KEYCLOAK_AUTO_PROVISION=true
      - KEYCLOAK_SYNC_USER_DATA=true
      - KEYCLOAK_ENABLE_ROLE_MAPPING=true
      - KEYCLOAK_ALLOW_LOCAL_AUTH=true
      - KEYCLOAK_FALLBACK_ON_ERROR=true

    volumes:
      - ./krayin-data:/var/www/html/storage
      - ./config:/var/www/html/config
    ports:
      - "8000:80"
    depends_on:
      - mysql
      - keycloak
    networks:
      - krayin-network

  mysql:
    image: mysql:8.0
    container_name: krayin-mysql
    environment:
      - MYSQL_DATABASE=krayin
      - MYSQL_USER=krayin
      - MYSQL_PASSWORD=${DB_PASSWORD}
      - MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - krayin-network

  keycloak:
    image: quay.io/keycloak/keycloak:latest
    container_name: keycloak-server
    environment:
      - KEYCLOAK_ADMIN=admin
      - KEYCLOAK_ADMIN_PASSWORD=${KEYCLOAK_ADMIN_PASSWORD}
      - KC_DB=postgres
      - KC_DB_URL=jdbc:postgresql://postgres:5432/keycloak
      - KC_DB_USERNAME=keycloak
      - KC_DB_PASSWORD=${KEYCLOAK_DB_PASSWORD}
      - KC_HOSTNAME=keycloak.example.com
    command: start-dev
    ports:
      - "8080:8080"
    depends_on:
      - postgres
    networks:
      - krayin-network

  postgres:
    image: postgres:15
    container_name: keycloak-postgres
    environment:
      - POSTGRES_DB=keycloak
      - POSTGRES_USER=keycloak
      - POSTGRES_PASSWORD=${KEYCLOAK_DB_PASSWORD}
    volumes:
      - postgres-data:/var/lib/postgresql/data
    networks:
      - krayin-network

volumes:
  mysql-data:
  postgres-data:

networks:
  krayin-network:
    driver: bridge
```

**Step 3: Create .env file**

```bash
# .env
DB_PASSWORD=secure_password_here
MYSQL_ROOT_PASSWORD=secure_root_password
KEYCLOAK_CLIENT_SECRET=your_keycloak_client_secret
KEYCLOAK_ADMIN_PASSWORD=admin_password
KEYCLOAK_DB_PASSWORD=keycloak_db_password
```

**Step 4: Deploy**

```bash
# Start containers
docker-compose up -d

# Run migrations and publish assets
docker-compose exec krayin php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider"
docker-compose exec krayin php artisan migrate --force

# Clear cache
docker-compose exec krayin php artisan config:clear
docker-compose exec krayin php artisan cache:clear
```

### Option 2: Multi-Stage Build

For optimized production containers:

```dockerfile
# Build stage
FROM composer:2 as builder

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --classmap-authoritative

# Production stage
FROM php:8.2-fpm-alpine

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql bcmath

# Install nginx
RUN apk add --no-cache nginx

# Copy application from builder
COPY --from=builder /app /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Expose port
EXPOSE 80

# Start services
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
```

### Option 3: Deployment Script

Create a deployment script for automated setup:

```bash
#!/bin/bash
# deploy-keycloak-sso.sh

set -e

echo "🚀 Deploying Keycloak SSO Extension..."

# Install via Composer
echo "📦 Installing package..."
docker-compose exec -T krayin composer require webkul/laravel-keycloak-sso

# Publish configuration
echo "📋 Publishing configuration..."
docker-compose exec -T krayin php artisan vendor:publish \
    --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider" \
    --force

# Run migrations
echo "🗄️  Running migrations..."
docker-compose exec -T krayin php artisan migrate --force

# Clear cache
echo "🧹 Clearing cache..."
docker-compose exec -T krayin php artisan config:clear
docker-compose exec -T krayin php artisan cache:clear
docker-compose exec -T krayin php artisan view:clear
docker-compose exec -T krayin php artisan route:clear

# Optimize for production
echo "⚡ Optimizing..."
docker-compose exec -T krayin php artisan config:cache
docker-compose exec -T krayin php artisan route:cache
docker-compose exec -T krayin php artisan view:cache

echo "✅ Keycloak SSO Extension deployed successfully!"
echo ""
echo "Next steps:"
echo "1. Configure Keycloak server (see INSTALLATION.md)"
echo "2. Update environment variables"
echo "3. Test connection: docker-compose exec krayin php artisan tinker"
echo "4. Visit your CRM and test login"
```

Make it executable:

```bash
chmod +x deploy-keycloak-sso.sh
./deploy-keycloak-sso.sh
```

## Kubernetes Deployment

### Deployment Configuration

```yaml
# keycloak-sso-config.yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: keycloak-sso-config
  namespace: krayin
data:
  KEYCLOAK_ENABLED: "true"
  KEYCLOAK_BASE_URL: "https://keycloak.example.com"
  KEYCLOAK_REALM: "production"
  KEYCLOAK_CLIENT_ID: "krayin-crm"
  KEYCLOAK_REDIRECT_URI: "https://crm.example.com/admin/auth/keycloak/callback"
  KEYCLOAK_AUTO_PROVISION: "true"
  KEYCLOAK_SYNC_USER_DATA: "true"
  KEYCLOAK_ENABLE_ROLE_MAPPING: "true"
  KEYCLOAK_ALLOW_LOCAL_AUTH: "true"
  KEYCLOAK_FALLBACK_ON_ERROR: "true"

---
apiVersion: v1
kind: Secret
metadata:
  name: keycloak-sso-secret
  namespace: krayin
type: Opaque
stringData:
  KEYCLOAK_CLIENT_SECRET: "your-client-secret-here"

---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: krayin-crm
  namespace: krayin
spec:
  replicas: 3
  selector:
    matchLabels:
      app: krayin-crm
  template:
    metadata:
      labels:
        app: krayin-crm
    spec:
      initContainers:
      - name: install-keycloak-sso
        image: webkul/krayin:latest
        command: ["/bin/sh", "-c"]
        args:
          - |
            composer require webkul/laravel-keycloak-sso
            php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider" --force
            php artisan migrate --force
        volumeMounts:
        - name: krayin-storage
          mountPath: /var/www/html/storage

      containers:
      - name: krayin
        image: webkul/krayin:latest
        envFrom:
        - configMapRef:
            name: keycloak-sso-config
        - secretRef:
            name: keycloak-sso-secret
        ports:
        - containerPort: 80
        volumeMounts:
        - name: krayin-storage
          mountPath: /var/www/html/storage
        livenessProbe:
          httpGet:
            path: /health
            port: 80
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /health
            port: 80
          initialDelaySeconds: 10
          periodSeconds: 5

      volumes:
      - name: krayin-storage
        persistentVolumeClaim:
          claimName: krayin-storage-pvc

---
apiVersion: v1
kind: Service
metadata:
  name: krayin-crm-service
  namespace: krayin
spec:
  selector:
    app: krayin-crm
  ports:
  - protocol: TCP
    port: 80
    targetPort: 80
  type: LoadBalancer
```

### Deploy to Kubernetes

```bash
# Create namespace
kubectl create namespace krayin

# Apply configuration
kubectl apply -f keycloak-sso-config.yaml

# Check deployment status
kubectl get pods -n krayin
kubectl logs -f deployment/krayin-crm -n krayin

# Run migrations
kubectl exec -it deployment/krayin-crm -n krayin -- php artisan migrate --force

# Clear cache
kubectl exec -it deployment/krayin-crm -n krayin -- php artisan cache:clear
```

## Deployment Checklist

### Pre-Deployment

- [ ] Keycloak server is running and accessible
- [ ] Keycloak realm created
- [ ] Keycloak client configured (confidential, standard flow enabled)
- [ ] Client secret obtained
- [ ] Valid redirect URIs configured in Keycloak
- [ ] Database backup created
- [ ] Environment variables prepared
- [ ] SSL/TLS certificates configured (production)

### Deployment Steps

- [ ] Install package via Composer
- [ ] Publish configuration and assets
- [ ] Run migrations
- [ ] Configure environment variables
- [ ] Clear all caches
- [ ] Test database connectivity
- [ ] Test Keycloak connectivity
- [ ] Configure role mappings
- [ ] Test login flow
- [ ] Test logout flow
- [ ] Test token refresh
- [ ] Test fallback to local auth

### Post-Deployment

- [ ] Verify all routes are registered (`php artisan route:list | grep keycloak`)
- [ ] Check logs for errors
- [ ] Test with different user roles
- [ ] Verify role mapping is working
- [ ] Test auto-provisioning (if enabled)
- [ ] Monitor performance
- [ ] Set up monitoring/alerting
- [ ] Document configuration for team
- [ ] Create rollback plan

### Production-Specific

- [ ] HTTPS enforced
- [ ] Error details hidden (`KEYCLOAK_SHOW_ERROR_DETAILS=false`)
- [ ] Caching enabled (`KEYCLOAK_CACHE_USER_INFO=true`)
- [ ] Session security configured
- [ ] Rate limiting configured
- [ ] Monitoring and logging set up
- [ ] Backup strategy in place
- [ ] Disaster recovery plan documented

## Environment-Specific Configuration

### Development

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_BASE_URL=http://localhost:8080
KEYCLOAK_REALM=development
KEYCLOAK_REDIRECT_URI=http://localhost:8000/admin/auth/keycloak/callback
KEYCLOAK_SHOW_ERROR_DETAILS=true
KEYCLOAK_LOG_STACK_TRACES=true
KEYCLOAK_ALLOW_LOCAL_AUTH=true
KEYCLOAK_FALLBACK_ON_ERROR=true
KEYCLOAK_CACHE_USER_INFO=false
```

### Staging

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_BASE_URL=https://keycloak-staging.example.com
KEYCLOAK_REALM=staging
KEYCLOAK_REDIRECT_URI=https://crm-staging.example.com/admin/auth/keycloak/callback
KEYCLOAK_SHOW_ERROR_DETAILS=false
KEYCLOAK_LOG_STACK_TRACES=true
KEYCLOAK_ALLOW_LOCAL_AUTH=true
KEYCLOAK_FALLBACK_ON_ERROR=true
KEYCLOAK_CACHE_USER_INFO=true
KEYCLOAK_CACHE_TTL=300
```

### Production

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_BASE_URL=https://keycloak.company.com
KEYCLOAK_REALM=production
KEYCLOAK_REDIRECT_URI=https://crm.company.com/admin/auth/keycloak/callback
KEYCLOAK_SHOW_ERROR_DETAILS=false
KEYCLOAK_LOG_STACK_TRACES=true
KEYCLOAK_ALLOW_LOCAL_AUTH=true
KEYCLOAK_FALLBACK_ON_ERROR=true
KEYCLOAK_CACHE_USER_INFO=true
KEYCLOAK_CACHE_TTL=600
KEYCLOAK_HTTP_TIMEOUT=30
KEYCLOAK_MAX_RETRIES=3
KEYCLOAK_EXPONENTIAL_BACKOFF=true
```

## Troubleshooting Deployment

### Issue: Package Not Found

```bash
# Clear composer cache
docker-compose exec krayin composer clearcache

# Try installing again
docker-compose exec krayin composer require webkul/laravel-keycloak-sso
```

### Issue: Migrations Fail

```bash
# Check migration status
docker-compose exec krayin php artisan migrate:status

# Roll back and retry
docker-compose exec krayin php artisan migrate:rollback
docker-compose exec krayin php artisan migrate --force
```

### Issue: Configuration Not Loading

```bash
# Clear all caches
docker-compose exec krayin php artisan config:clear
docker-compose exec krayin php artisan cache:clear
docker-compose exec krayin php artisan route:clear
docker-compose exec krayin php artisan view:clear

# Verify configuration
docker-compose exec krayin php artisan tinker
>>> config('keycloak.enabled')
```

### Issue: Routes Not Working

```bash
# Verify routes are loaded
docker-compose exec krayin php artisan route:list | grep keycloak

# If missing, check service provider
docker-compose exec krayin php artisan tinker
>>> app()->getProviders('Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider')
```

### Issue: Permission Denied

```bash
# Fix permissions
docker-compose exec krayin chown -R www-data:www-data storage bootstrap/cache
docker-compose exec krayin chmod -R 775 storage bootstrap/cache
```

## Automated Deployment

### GitHub Actions CI/CD

```yaml
# .github/workflows/deploy.yml
name: Deploy Keycloak SSO

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Deploy to production
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          HOST: ${{ secrets.HOST }}
        run: |
          echo "$SSH_PRIVATE_KEY" > key.pem
          chmod 600 key.pem
          ssh -i key.pem user@$HOST << 'EOF'
            cd /var/www/krayin
            docker-compose exec -T krayin composer update webkul/laravel-keycloak-sso
            docker-compose exec -T krayin php artisan migrate --force
            docker-compose exec -T krayin php artisan cache:clear
            docker-compose exec -T krayin php artisan config:cache
          EOF
```

## Support

For deployment assistance:
- 📖 [Installation Guide](INSTALLATION.md)
- 📖 [Configuration Guide](CONFIGURATION.md)
- 🐛 [Issue Tracker](https://github.com/kennis-ai/krayin-crm-keycloak/issues)
- 📧 Email: suporte@kennis.com.br
