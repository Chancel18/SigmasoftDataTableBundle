import clsx from 'clsx';
import Link from '@docusaurus/Link';
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import Layout from '@theme/Layout';
import HomepageFeatures from '@site/src/components/HomepageFeatures';

import styles from './index.module.css';

function HomepageHeader() {
  const {siteConfig} = useDocusaurusContext();
  return (
    <header className={clsx('hero hero--primary', styles.heroBanner)}>
      <div className="container">
        <h1 className="hero__title">{siteConfig.title}</h1>
        <p className="hero__subtitle">{siteConfig.tagline}</p>
        <div className={styles.buttons}>
          <Link
            className="button button--secondary button--lg"
            to="/docs/installation">
            🚀 Commencer - 5min ⏱️
          </Link>
          <Link
            className="button button--outline button--secondary button--lg margin-left--md"
            to="/docs/quick-start">
            📖 Guide Rapide
          </Link>
        </div>
        <div className={styles.versionBadge}>
          <span className="version-badge">v2.0.5 STABLE</span>
        </div>
        <div className={styles.badges}>
          <img src="https://img.shields.io/packagist/v/sigmasoft/datatable-bundle" alt="Version" />
          <img src="https://img.shields.io/packagist/dt/sigmasoft/datatable-bundle" alt="Downloads" />
          <img src="https://img.shields.io/github/license/Chancel18/SigmasoftDataTableBundle" alt="License" />
          <img src="https://img.shields.io/github/stars/Chancel18/SigmasoftDataTableBundle" alt="Stars" />
        </div>
      </div>
    </header>
  );
}

export default function Home() {
  const {siteConfig} = useDocusaurusContext();
  return (
    <Layout
      title={`Accueil`}
      description="Bundle Symfony moderne pour créer des DataTables interactives avec Live Components, génération automatique et temps réel">
      <HomepageHeader />
      <main>
        <HomepageFeatures />
        
        {/* Quick Start Section */}
        <section className={styles.quickStart}>
          <div className="container">
            <div className="row">
              <div className="col col--12">
                <h2 className="text--center margin-bottom--lg">
                  ⚡ Installation en 30 secondes
                </h2>
              </div>
            </div>
            <div className="row">
              <div className="col col--6">
                <div className="command-line">
                  composer require sigmasoft/datatable-bundle
                </div>
              </div>
              <div className="col col--6">
                <div className="command-line">
                  php bin/console make:datatable User --controller
                </div>
              </div>
            </div>
            <div className="row margin-top--lg">
              <div className="col col--12">
                <h3 className="text--center">Puis dans votre template Twig :</h3>
                <div className="highlight-box highlight-box--tip">
                  <code>{'<twig:SigmasoftDataTable entityClass="App\\Entity\\User" />'}</code>
                </div>
                <p className="text--center">
                  <strong>C'est tout ! 🎉</strong> Votre DataTable Bootstrap responsive est prête !
                </p>
              </div>
            </div>
          </div>
        </section>

        {/* Stats Section */}
        <section className={styles.stats}>
          <div className="container">
            <div className="row">
              <div className="col col--3">
                <div className="text--center">
                  <h3>14</h3>
                  <p>Tests Unitaires</p>
                </div>
              </div>
              <div className="col col--3">
                <div className="text--center">
                  <h3>100%</h3>
                  <p>Tests Passés</p>
                </div>
              </div>
              <div className="col col--3">
                <div className="text--center">
                  <h3>v2.0.5</h3>
                  <p>Version Stable</p>
                </div>
              </div>
              <div className="col col--3">
                <div className="text--center">
                  <h3>Production</h3>
                  <p>Ready</p>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
    </Layout>
  );
}