---
sidebar_position: 1
---

# Déploiement GitHub Pages

Guide complet pour déployer la documentation Docusaurus sur GitHub Pages. 🚀

## Configuration Automatique

Le SigmasoftDataTableBundle inclut une configuration GitHub Actions pour déployer automatiquement la documentation sur GitHub Pages.

### Fichier de Workflow

```yaml title=".github/workflows/documentation.yml"
name: Deploy Documentation

on:
  push:
    branches: [ main, master ]
  pull_request:
    branches: [ main, master ]

permissions:
  contents: read
  pages: write
  id-token: write

concurrency:
  group: "pages"
  cancel-in-progress: false

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: 18
          cache: npm
          cache-dependency-path: docs/package-lock.json

      - name: Install dependencies
        run: npm ci
        working-directory: docs

      - name: Build website
        run: npm run build
        working-directory: docs

      - name: Setup Pages
        uses: actions/configure-pages@v4

      - name: Upload artifact
        uses: actions/upload-pages-artifact@v3
        with:
          path: docs/build

  deploy:
    environment:
      name: github-pages
      url: ${{ steps.deployment.outputs.page_url }}
    runs-on: ubuntu-latest
    needs: build
    steps:
      - name: Deploy to GitHub Pages
        id: deployment
        uses: actions/deploy-pages@v4
```

## Configuration du Repository

### 1. Activer GitHub Pages

1. Allez dans **Settings** → **Pages** de votre repository
2. Sélectionnez **GitHub Actions** comme source
3. La documentation sera accessible à `https://chancel18.github.io/SigmasoftDataTableBundle/`

### 2. Configuration Docusaurus

Le fichier `docusaurus.config.js` est pré-configuré pour GitHub Pages :

```javascript
const config = {
  title: 'SigmasoftDataTableBundle',
  tagline: 'Bundle Symfony moderne pour tables de données interactives',
  favicon: 'img/favicon.ico',

  // Configuration GitHub Pages
  url: 'https://chancel18.github.io',
  baseUrl: '/SigmasoftDataTableBundle/',
  organizationName: 'Chancel18',
  projectName: 'SigmasoftDataTableBundle',
  
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',

  // Configuration du déploiement
  deploymentBranch: 'gh-pages',
  trailingSlash: false,
};
```

## Variables d'Environnement

### Variables Repository

Configurez ces variables dans **Settings** → **Secrets and variables** → **Actions** :

```bash
# URL de base du site
DOCUSAURUS_URL=https://chancel18.github.io
DOCUSAURUS_BASE_URL=/SigmasoftDataTableBundle/

# Informations de contact
SUPPORT_EMAIL=support@sigmasoft-solution.com
AUTHOR_EMAIL=g.makela@sigmasoft-solution.com

# Analytics (optionnel)
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
```

## Déploiement Manuel

### Prérequis

```bash
# Node.js 18+
node --version

# npm ou yarn
npm --version
```

### Installation et Build

```bash
# Aller dans le dossier docs
cd docs

# Installer les dépendances
npm install

# Build de production
npm run build

# Serveur de développement (optionnel)
npm run start
```

### Déploiement via CLI

```bash
# Déploiement direct
npm run deploy

# Ou avec paramètres spécifiques
GIT_USER=Chancel18 npm run deploy
```

## Configuration Avancée

### Custom Domain

Pour utiliser un domaine personnalisé :

1. Créez un fichier `docs/static/CNAME` :
```
docs.sigmasoft-solution.com
```

2. Configurez votre DNS :
```
CNAME docs.sigmasoft-solution.com chancel18.github.io
```

3. Mettez à jour `docusaurus.config.js` :
```javascript
const config = {
  url: 'https://docs.sigmasoft-solution.com',
  baseUrl: '/',
  // ...
};
```

### Analytics et SEO

```javascript title="docs/docusaurus.config.js"
const config = {
  // Google Analytics
  gtag: {
    trackingID: 'G-XXXXXXXXXX',
    anonymizeIP: true,
  },
  
  // SEO amélioré
  metadata: [
    {name: 'keywords', content: 'symfony, datatable, bundle, php, javascript'},
    {name: 'description', content: 'Bundle Symfony moderne pour tables de données interactives avec édition inline'},
    {property: 'og:image', content: 'https://chancel18.github.io/SigmasoftDataTableBundle/img/social-card.png'},
  ],
  
  // Sitemap
  plugins: [
    [
      '@docusaurus/plugin-sitemap',
      {
        changefreq: 'weekly',
        priority: 0.5,
        ignorePatterns: ['/tags/**'],
        filename: 'sitemap.xml',
      },
    ],
  ],
};
```

### Configuration Multi-langue

```javascript title="docs/docusaurus.config.js"
const config = {
  i18n: {
    defaultLocale: 'fr',
    locales: ['fr', 'en'],
    localeConfigs: {
      fr: {
        label: 'Français',
        direction: 'ltr',
      },
      en: {
        label: 'English',
        direction: 'ltr',
      },
    },
  },
};
```

## Optimisation des Performances

### Configuration PWA

```javascript title="docs/docusaurus.config.js"
const config = {
  plugins: [
    [
      '@docusaurus/plugin-pwa',
      {
        debug: false,
        offlineModeActivationStrategies: [
          'appInstalled',
          'standalone',
          'queryString',
        ],
        pwaHead: [
          {
            tagName: 'link',
            rel: 'icon',
            href: '/img/docusaurus.png',
          },
          {
            tagName: 'link',
            rel: 'manifest',
            href: '/manifest.json',
          },
          {
            tagName: 'meta',
            name: 'theme-color',
            content: 'rgb(37, 194, 160)',
          },
        ],
      },
    ],
  ],
};
```

### Compression et Cache

```javascript title="docs/docusaurus.config.js"
const config = {
  webpack: {
    jsLoader: (isServer) => ({
      loader: 'esbuild-loader',
      options: {
        loader: 'tsx',
        format: isServer ? 'cjs' : undefined,
        target: isServer ? 'node12' : 'es2017',
      },
    }),
  },
  
  plugins: [
    [
      '@docusaurus/plugin-client-redirects',
      {
        redirects: [
          {
            to: '/docs/introduction',
            from: ['/docs', '/docs/'],
          },
        ],
      },
    ],
  ],
};
```

## Monitoring et Analytics

### GitHub Insights

Le workflow inclut des métriques automatiques :

```yaml
- name: Generate deployment report
  run: |
    echo "## Deployment Report" >> $GITHUB_STEP_SUMMARY
    echo "- Build time: $(date)" >> $GITHUB_STEP_SUMMARY
    echo "- Commit: ${{ github.sha }}" >> $GITHUB_STEP_SUMMARY
    echo "- Branch: ${{ github.ref_name }}" >> $GITHUB_STEP_SUMMARY
    echo "- URL: https://chancel18.github.io/SigmasoftDataTableBundle/" >> $GITHUB_STEP_SUMMARY
```

### Uptime Monitoring

Configurez un service de monitoring comme Uptime Robot :

```bash
# URL à surveiller
https://chancel18.github.io/SigmasoftDataTableBundle/

# Fréquence de vérification
Toutes les 5 minutes

# Alertes
Email: support@sigmasoft-solution.com
```

## Dépannage

### Erreurs Communes

#### Build Failed
```bash
# Vérifier les dépendances
npm audit
npm audit fix

# Nettoyer le cache
npm run clear
npm run build
```

#### 404 sur GitHub Pages
```javascript
// Vérifier baseUrl dans docusaurus.config.js
baseUrl: '/SigmasoftDataTableBundle/', // Doit correspondre au nom du repo
```

#### CSS/JS Non Chargés
```javascript
// Vérifier l'URL dans docusaurus.config.js
url: 'https://chancel18.github.io', // Sans slash final
baseUrl: '/SigmasoftDataTableBundle/', // Avec slash initial et final
```

### Logs et Debug

```bash
# Mode debug local
npm run start -- --host 0.0.0.0 --port 3000

# Build avec logs détaillés
npm run build -- --out-dir build --config docusaurus.config.js
```

## Sécurité

### Configuration CSP

```javascript title="docs/docusaurus.config.js"
const config = {
  customFields: {
    csp: {
      'default-src': ["'self'"],
      'script-src': ["'self'", "'unsafe-inline'", 'https://www.googletagmanager.com'],
      'style-src': ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com'],
      'font-src': ["'self'", 'https://fonts.gstatic.com'],
      'img-src': ["'self'", 'data:', 'https:'],
    },
  },
};
```

### Variables Sensibles

Ne jamais commiter :
- Clés API privées
- Tokens d'accès
- Informations de base de données
- Certificats privés

Utilisez GitHub Secrets pour les données sensibles.

---

## Support et Ressources

### Documentation Officielle
- 📖 **Docusaurus** : [Documentation officielle](https://docusaurus.io/)
- 🚀 **GitHub Pages** : [Guide officiel](https://pages.github.com/)
- ⚙️ **GitHub Actions** : [Documentation](https://docs.github.com/en/actions)

### Support Technique
- 🐛 **Issues** : [Signaler un problème](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- 📧 **Contact** : [support@sigmasoft-solution.com](mailto:support@sigmasoft-solution.com)

---

*Documentation de déploiement rédigée par [Gédéon MAKELA](mailto:g.makela@sigmasoft-solution.com) - [Sigmasoft Solutions](https://sigmasoft-solution.com)*