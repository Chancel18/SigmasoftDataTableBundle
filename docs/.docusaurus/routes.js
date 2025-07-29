import React from 'react';
import ComponentCreator from '@docusaurus/ComponentCreator';

export default [
  {
    path: '/SigmasoftDataTableBundle/__docusaurus/debug',
    component: ComponentCreator('/SigmasoftDataTableBundle/__docusaurus/debug', 'e7f'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/__docusaurus/debug/config',
    component: ComponentCreator('/SigmasoftDataTableBundle/__docusaurus/debug/config', '898'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/__docusaurus/debug/content',
    component: ComponentCreator('/SigmasoftDataTableBundle/__docusaurus/debug/content', 'da0'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/__docusaurus/debug/globalData',
    component: ComponentCreator('/SigmasoftDataTableBundle/__docusaurus/debug/globalData', '6e7'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/__docusaurus/debug/metadata',
    component: ComponentCreator('/SigmasoftDataTableBundle/__docusaurus/debug/metadata', '030'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/__docusaurus/debug/registry',
    component: ComponentCreator('/SigmasoftDataTableBundle/__docusaurus/debug/registry', 'aa6'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/__docusaurus/debug/routes',
    component: ComponentCreator('/SigmasoftDataTableBundle/__docusaurus/debug/routes', '870'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/docs',
    component: ComponentCreator('/SigmasoftDataTableBundle/docs', '620'),
    routes: [
      {
        path: '/SigmasoftDataTableBundle/docs',
        component: ComponentCreator('/SigmasoftDataTableBundle/docs', '4da'),
        routes: [
          {
            path: '/SigmasoftDataTableBundle/docs',
            component: ComponentCreator('/SigmasoftDataTableBundle/docs', 'e58'),
            routes: [
              {
                path: '/SigmasoftDataTableBundle/docs/api/overview',
                component: ComponentCreator('/SigmasoftDataTableBundle/docs/api/overview', 'bc0'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/docs/deployment/github-pages',
                component: ComponentCreator('/SigmasoftDataTableBundle/docs/deployment/github-pages', '35e'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/docs/developer-guide/architecture',
                component: ComponentCreator('/SigmasoftDataTableBundle/docs/developer-guide/architecture', 'b5a'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/docs/installation',
                component: ComponentCreator('/SigmasoftDataTableBundle/docs/installation', '029'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/docs/introduction',
                component: ComponentCreator('/SigmasoftDataTableBundle/docs/introduction', 'cf0'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/docs/quick-start',
                component: ComponentCreator('/SigmasoftDataTableBundle/docs/quick-start', '6c5'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/docs/user-guide/basic-usage',
                component: ComponentCreator('/SigmasoftDataTableBundle/docs/user-guide/basic-usage', '1c6'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/docs/user-guide/customization',
                component: ComponentCreator('/SigmasoftDataTableBundle/docs/user-guide/customization', 'a4a'),
                exact: true,
                sidebar: "tutorialSidebar"
              }
            ]
          }
        ]
      }
    ]
  },
  {
    path: '/SigmasoftDataTableBundle/',
    component: ComponentCreator('/SigmasoftDataTableBundle/', '6a2'),
    exact: true
  },
  {
    path: '*',
    component: ComponentCreator('*'),
  },
];
