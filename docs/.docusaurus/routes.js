import React from 'react';
import ComponentCreator from '@docusaurus/ComponentCreator';

export default [
  {
    path: '/SigmasoftDataTableBundle/en/blog',
    component: ComponentCreator('/SigmasoftDataTableBundle/en/blog', 'f1f'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/en/blog/archive',
    component: ComponentCreator('/SigmasoftDataTableBundle/en/blog/archive', 'd7b'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/en/blog/tags',
    component: ComponentCreator('/SigmasoftDataTableBundle/en/blog/tags', '92d'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/en/blog/tags/annonce',
    component: ComponentCreator('/SigmasoftDataTableBundle/en/blog/tags/annonce', 'd84'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/en/blog/tags/datatable',
    component: ComponentCreator('/SigmasoftDataTableBundle/en/blog/tags/datatable', '33c'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/en/blog/tags/symfony',
    component: ComponentCreator('/SigmasoftDataTableBundle/en/blog/tags/symfony', '621'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/en/blog/welcome',
    component: ComponentCreator('/SigmasoftDataTableBundle/en/blog/welcome', '9f7'),
    exact: true
  },
  {
    path: '/SigmasoftDataTableBundle/en/docs',
    component: ComponentCreator('/SigmasoftDataTableBundle/en/docs', 'c0f'),
    routes: [
      {
        path: '/SigmasoftDataTableBundle/en/docs',
        component: ComponentCreator('/SigmasoftDataTableBundle/en/docs', 'e27'),
        routes: [
          {
            path: '/SigmasoftDataTableBundle/en/docs',
            component: ComponentCreator('/SigmasoftDataTableBundle/en/docs', 'fcf'),
            routes: [
              {
                path: '/SigmasoftDataTableBundle/en/docs/api/overview',
                component: ComponentCreator('/SigmasoftDataTableBundle/en/docs/api/overview', '70f'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/en/docs/deployment/github-pages',
                component: ComponentCreator('/SigmasoftDataTableBundle/en/docs/deployment/github-pages', 'd55'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/en/docs/developer-guide/architecture',
                component: ComponentCreator('/SigmasoftDataTableBundle/en/docs/developer-guide/architecture', '67d'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/en/docs/installation',
                component: ComponentCreator('/SigmasoftDataTableBundle/en/docs/installation', 'f8c'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/en/docs/introduction',
                component: ComponentCreator('/SigmasoftDataTableBundle/en/docs/introduction', '61e'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/en/docs/quick-start',
                component: ComponentCreator('/SigmasoftDataTableBundle/en/docs/quick-start', '30e'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/en/docs/user-guide/basic-usage',
                component: ComponentCreator('/SigmasoftDataTableBundle/en/docs/user-guide/basic-usage', 'bae'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/SigmasoftDataTableBundle/en/docs/user-guide/customization',
                component: ComponentCreator('/SigmasoftDataTableBundle/en/docs/user-guide/customization', '563'),
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
    path: '/SigmasoftDataTableBundle/en/',
    component: ComponentCreator('/SigmasoftDataTableBundle/en/', '0ca'),
    exact: true
  },
  {
    path: '*',
    component: ComponentCreator('*'),
  },
];
