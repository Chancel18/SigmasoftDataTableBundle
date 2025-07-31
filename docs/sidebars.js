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
        'migration-v2.3',
      ],
    },
    {
      type: 'category',
      label: '📋 Guide Utilisateur',
      items: [
        'user-guide/basic-usage',
        'user-guide/customization',
      ],
    },
    {
      type: 'category',
      label: '🏗️ Guide Développeur',
      items: [
        'developer-guide/architecture',
      ],
    },
    {
      type: 'category',
      label: '📖 API Reference',
      items: [
        'api/overview',
      ],
    },
    {
      type: 'category',
      label: '🚢 Déploiement',
      items: [
        'deployment/github-pages',
      ],
    },
    {
      type: 'category',
      label: '📋 Ressources',
      items: [
        'changelog',
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

module.exports = sidebars;