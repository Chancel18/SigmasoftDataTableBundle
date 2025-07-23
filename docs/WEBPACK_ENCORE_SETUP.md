# 🔧 Configuration Webpack Encore pour SigmasoftDataTableBundle

## 🚨 Résolution Erreur Manifest.json

### Erreur Rencontrée
```
Asset manifest file "public/build/manifest.json" does not exist. 
Did you forget to build the assets with npm or yarn?
```

## ✅ Solutions Rapides

### Option 1 : Installation & Build Complet

```bash
# 1. Aller dans votre projet Symfony
cd path/to/your/symfony/project

# 2. Installer Webpack Encore si pas encore fait
composer require symfony/webpack-encore-bundle

# 3. Initialiser npm/yarn
npm init -y
# ou
yarn init -y

# 4. Installer Webpack Encore
npm install @symfony/webpack-encore --save-dev
# ou
yarn add @symfony/webpack-encore --dev

# 5. Initialiser la configuration
npx encore init
# ou  
yarn encore init

# 6. Builder les assets
npm run dev
# ou
yarn dev
```

### Option 2 : Configuration Manuelle

#### 1. Package.json basique
```json
{
  "name": "symfony-project",
  "version": "1.0.0",
  "scripts": {
    "dev": "encore dev",
    "watch": "encore dev --watch",
    "build": "encore production"
  },
  "devDependencies": {
    "@symfony/webpack-encore": "^4.0",
    "webpack": "^5.0",
    "webpack-cli": "^5.0"
  }
}
```

#### 2. Webpack.config.js basique
```javascript
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
;

module.exports = Encore.getWebpackConfig();
```

#### 3. Assets de base
```javascript
// assets/app.js
import './styles/app.css';

console.log('Hello Webpack Encore! Edit me in assets/app.js');
```

```css
/* assets/styles/app.css */
body {
    background-color: lightgray;
}
```

### Option 3 : Bypass Temporaire

#### Modifier base.html.twig
```twig
{# AVANT - avec Encore #}
{% block stylesheets %}
    {{ encore_entry_link_tags('app') }}
{% endblock %}

{% block javascripts %}
    {{ encore_entry_script_tags('app') }}
{% endblock %}

{# APRÈS - sans Encore (TEMPORAIRE) #}
{% block stylesheets %}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
{% endblock %}

{% block javascripts %}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
{% endblock %}
```

## 🎯 Configuration Recommandée pour DataTable

### Assets optimaux pour SigmasoftDataTableBundle

#### Package.json avec dépendances DataTable
```json
{
  "name": "symfony-datatable-project",
  "scripts": {
    "dev": "encore dev",
    "watch": "encore dev --watch",
    "build": "encore production"
  },
  "devDependencies": {
    "@symfony/webpack-encore": "^4.0",
    "webpack": "^5.0",
    "webpack-cli": "^5.0",
    "css-loader": "^6.0",
    "file-loader": "^6.0",
    "sass": "^1.0",
    "sass-loader": "^13.0"
  },
  "dependencies": {
    "bootstrap": "^5.3.0",
    "@fortawesome/fontawesome-free": "^6.0.0"
  }
}
```

#### Webpack.config.js optimisé
```javascript
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    
    // Entrées principales
    .addEntry('app', './assets/app.js')
    .addEntry('datatable', './assets/datatable.js')
    
    // Configuration
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    // Loaders
    .enableSassLoader()
    .autoProvidejQuery()
    
    // Babel
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
;

module.exports = Encore.getWebpackConfig();
```

#### Assets DataTable spécifiques
```javascript
// assets/datatable.js
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import './styles/datatable.scss';

import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Fonctionnalités DataTable spécifiques
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Autres initialisations DataTable...
});
```

```scss
// assets/styles/datatable.scss
.datatable-container {
    .table {
        th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.75em;
        }
    }
    
    .pagination {
        .page-link {
            border-radius: 0.25rem;
            margin: 0 2px;
        }
    }
    
    .search-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 0.5rem 0.5rem 0 0;
        
        .form-control {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    }
}
```

#### Template avec assets DataTable
```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{% block title %}Welcome!{% endblock %}</title>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>⚫️</text></svg>">
        
        {% block stylesheets %}
            {{ encore_entry_link_tags('app') }}
            {{ encore_entry_link_tags('datatable') }}
        {% endblock %}
    </head>
    <body>
        {% block body %}{% endblock %}
        
        {% block javascripts %}
            {{ encore_entry_script_tags('app') }}
            {{ encore_entry_script_tags('datatable') }}
        {% endblock %}
    </body>
</html>
```

## 🚀 Commandes de Build

```bash
# Développement (avec watch)
npm run watch
# ou
yarn watch

# Production
npm run build
# ou 
yarn build

# Développement simple
npm run dev
# ou
yarn dev
```

## 🔍 Vérification

Après le build, vérifiez que ces fichiers existent :
- `public/build/manifest.json`
- `public/build/app.js`
- `public/build/app.css`
- `public/build/datatable.js` (si configuré)
- `public/build/datatable.css` (si configuré)

## 💡 Tips

1. **Toujours builder après modifications** des assets
2. **Utiliser `watch`** en développement pour auto-rebuild
3. **Vérifier les permissions** du dossier `public/build/`
4. **Nettoyer le cache Symfony** : `php bin/console cache:clear`

## 🆘 En cas de problème

```bash
# Nettoyer et recommencer
rm -rf node_modules public/build
npm install
npm run dev

# Vérifier les permissions
chmod -R 755 public/build

# Debug Encore
npm run dev -- --mode development --devtool eval-source-map
```