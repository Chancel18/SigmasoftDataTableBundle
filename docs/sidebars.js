/**
 * Creating a sidebar enables you to:
 - create an ordered group of docs
 - render a sidebar for each doc of that group
 - provide next/previous navigation

 The sidebars can be generated from the filesystem, or explicitly defined here.

 Create as many sidebars as you want.
 */

// @ts-check

/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  // By default, Docusaurus generates a sidebar from the docs folder structure
  tutorialSidebar: [
    'introduction',
    {
      type: 'category',
      label: '🚀 Démarrage',
      items: [
        'installation',
        'quick-start',
        'configuration',
      ],
    },
    {
      type: 'category',
      label: '📋 Guide Utilisateur',
      items: [
        'user-guide/basic-usage',
        'user-guide/maker-command',
        'user-guide/live-components',
        'user-guide/customization',
      ],
    },
    {
      type: 'category',
      label: '🏗️ Guide Développeur',
      items: [
        'developer-guide/architecture',
        'developer-guide/extending',
        'developer-guide/custom-renderers',
        'developer-guide/events',
      ],
    },
    {
      type: 'category',
      label: '📖 API Reference',
      items: [
        'api/overview',
        'api/datatable-builder',
        'api/live-component',
        'api/configuration',
        'api/services',
      ],
    },
    {
      type: 'category',
      label: '🧪 Tests & Qualité',
      items: [
        'testing/unit-tests',
        'testing/integration-tests',
        'testing/coverage',
      ],
    },
    {
      type: 'category',
      label: '🚢 Déploiement',
      items: [
        'deployment/production',
        'deployment/performance',
        'deployment/monitoring',
      ],
    },
    {
      type: 'category',
      label: '📚 Exemples',
      items: [
        'examples/basic-table',
        'examples/advanced-features',
        'examples/real-world',
      ],
    },
    {
      type: 'category',
      label: '🔧 Dépannage',
      items: [
        'troubleshooting/common-issues',
        'troubleshooting/faq',
        'troubleshooting/migration',
      ],
    },
  ],

  // But you can create a sidebar manually
  /*
  tutorialSidebar: [
    'intro',
    'hello',
    {
      type: 'category',
      label: 'Tutorial',
      items: ['tutorial-basics/create-a-document'],
    },
  ],
   */
};

export default sidebars;