// @ts-check
// `@type` JSDoc annotations allow editor autocompletion and type checking
// (when paired with `@ts-check`).
// There are various equivalent ways to declare your Docusaurus config.
// See: https://docusaurus.io/docs/api/docusaurus-config

const {themes: prismThemes} = require('prism-react-renderer');

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'SigmasoftDataTableBundle',
  tagline: 'Bundle Symfony moderne pour DataTables interactives avec Live Components',
  favicon: 'img/favicon.ico',

  // Set the production url of your site here
  url: 'https://chancel18.github.io',
  // Set the /<baseUrl>/ pathname under which your site is served
  // For GitHub pages deployment, it is often '/<projectName>/'
  baseUrl: '/SigmasoftDataTableBundle/',

  // GitHub pages deployment config.
  // If you aren't using GitHub pages, you don't need these.
  organizationName: 'Chancel18', // Usually your GitHub org/user name.
  projectName: 'SigmasoftDataTableBundle', // Usually your repo name.
  deploymentBranch: 'gh-pages',
  trailingSlash: false,

  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',

  // Even if you don't use internationalization, you can use this field to set
  // useful metadata like html lang. For example, if your site is Chinese, you
  // may want to set it to "zh-Hans".
  i18n: {
    defaultLocale: 'fr',
    locales: ['fr', 'en'],
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: './sidebars.js',
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          editUrl:
            'https://github.com/Chancel18/SigmasoftDataTableBundle/tree/master/docs/',
        },
        blog: {
          showReadingTime: true,
          feedOptions: {
            type: ['rss', 'atom'],
            xslt: true,
          },
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          editUrl:
            'https://github.com/Chancel18/SigmasoftDataTableBundle/tree/master/docs/',
          // Useful options to enforce blogging best practices
          onInlineTags: 'warn',
          onInlineAuthors: 'warn',
          onUntruncatedBlogPosts: 'warn',
        },
        theme: {
          customCss: './src/css/custom.css',
        },
      }),
    ],
  ],

  markdown: {
    mermaid: true,
  },
  themes: ['@docusaurus/theme-mermaid'],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      // Replace with your project's social card
      image: 'img/sigmasoft-datatable-social-card.jpg',
      navbar: {
        title: 'SigmasoftDataTableBundle',
        logo: {
          alt: 'Sigmasoft Logo',
          src: 'img/logo.svg',
        },
        items: [
          {
            type: 'docSidebar',
            sidebarId: 'tutorialSidebar',
            position: 'left',
            label: 'Documentation',
          },
          {to: '/blog', label: 'Blog', position: 'left'},
          {
            type: 'localeDropdown',
            position: 'right',
          },
          {
            href: 'https://github.com/Chancel18/SigmasoftDataTableBundle',
            label: 'GitHub',
            position: 'right',
          },
          {
            href: 'https://packagist.org/packages/sigmasoft/datatable-bundle',
            label: 'Packagist',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Documentation',
            items: [
              {
                label: 'Installation',
                to: '/docs/installation',
              },
              {
                label: 'Démarrage Rapide',
                to: '/docs/quick-start',
              },
              {
                label: 'API Reference',
                to: '/docs/api/overview',
              },
            ],
          },
          {
            title: 'Communauté',
            items: [
              {
                label: 'GitHub Issues',
                href: 'https://github.com/Chancel18/SigmasoftDataTableBundle/issues',
              },
              {
                label: 'Discussions',
                href: 'https://github.com/Chancel18/SigmasoftDataTableBundle/discussions',
              },
            ],
          },
          {
            title: 'Ressources',
            items: [
              {
                label: 'Packagist',
                href: 'https://packagist.org/packages/sigmasoft/datatable-bundle',
              },
              {
                label: 'Changelog',
                href: 'https://github.com/Chancel18/SigmasoftDataTableBundle/blob/master/CHANGELOG.md',
              },
              {
                label: 'Support Technique',
                href: 'mailto:support@sigmasoft-solution.com',
              },
              {
                label: 'Gédéon MAKELA',
                href: 'mailto:g.makela@sigmasoft-solution.com',
              },
              {
                label: 'Sigmasoft Solutions',
                href: 'https://sigmasoft-solution.com',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} Gédéon MAKELA - Sigmasoft Solutions. Built with Docusaurus.`,
      },
      prism: {
        theme: prismThemes.github,
        darkTheme: prismThemes.dracula,
        additionalLanguages: ['php', 'yaml', 'bash'],
      },
    }),
};

module.exports = config;