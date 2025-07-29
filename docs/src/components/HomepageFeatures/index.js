import clsx from 'clsx';
import styles from './styles.module.css';

const FeatureList = [
  {
    title: '🚀 Génération Automatique',
    Svg: require('@site/static/img/undraw_code_thinking.svg').default,
    description: (
      <>
        Commande <code>make:datatable</code> qui génère automatiquement
        votre DataTable avec détection des champs, types et relations Doctrine.
        Configuration YAML auto-générée et prête à l'emploi.
      </>
    ),
  },
  {
    title: '⚡ Live Components',
    Svg: require('@site/static/img/undraw_real_time_sync.svg').default,
    description: (
      <>
        Interactions temps réel avec Symfony UX Live Components.
        Recherche, tri, pagination sans rechargement de page.
        Bootstrap 5 responsive automatiquement généré.
      </>
    ),
  },
  {
    title: '🎯 Une Ligne de Code',
    Svg: require('@site/static/img/undraw_programming.svg').default,
    description: (
      <>
        <code>{'<twig:SigmasoftDataTable entityClass="App\\Entity\\User" />'}</code>
        <br />
        Une seule ligne suffit pour afficher un tableau complet avec toutes les fonctionnalités.
        Architecture moderne et performante.
      </>
    ),
  },
  {
    title: '🧪 Qualité Enterprise',
    Svg: require('@site/static/img/undraw_bug_fixing.svg').default,
    description: (
      <>
        14 tests unitaires, 100% de réussite. Bundle testé et validé
        pour la production. Standards Symfony 6+ respectés.
        Documentation complète et exemples concrets.
      </>
    ),
  },
  {
    title: '🔧 Personnalisable',
    Svg: require('@site/static/img/undraw_settings.svg').default,
    description: (
      <>
        Templates Twig customisables, renderers extensibles,
        événements Doctrine intégrés. Architecture modulaire
        permettant d'adapter le bundle à vos besoins spécifiques.
      </>
    ),
  },
  {
    title: '📊 Performance Optimisée',
    Svg: require('@site/static/img/undraw_speed_test.svg').default,
    description: (
      <>
        Requêtes Doctrine optimisées, cache intelligent,
        lazy loading des données. Pagination efficace
        et gestion mémoire optimisée pour les gros volumes.
      </>
    ),
  },
];

function Feature({Svg, title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Svg className={styles.featureSvg} role="img" />
      </div>
      <div className="text--center padding-horiz--md">
        <h3>{title}</h3>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          <div className="col col--12">
            <h2 className="text--center margin-bottom--lg">
              ✨ Pourquoi choisir SigmasoftDataTableBundle ?
            </h2>
          </div>
        </div>
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}