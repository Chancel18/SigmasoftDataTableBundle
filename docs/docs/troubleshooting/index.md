---
sidebar_position: 1
---

# Troubleshooting

Cette section contient des solutions aux problèmes courants rencontrés avec SigmasoftDataTableBundle.

## Guides de Dépannage

### 🔧 [Problèmes d'Installation](./installation-issues)
Solutions pour les problèmes rencontrés lors de l'installation du bundle :
- Commande make:datatable non disponible
- Services non trouvés
- Erreurs d'autoloading
- Problèmes de templates
- Incompatibilités de versions

### 📊 [Problèmes de DataTable](./datatable-issues)
Solutions pour les problèmes liés aux DataTables :
- Données non affichées
- Problèmes de pagination
- Erreurs de tri et recherche
- Problèmes de performance

### ✏️ [Problèmes d'Édition Inline](./inline-edit-issues)
Solutions pour les problèmes d'édition inline :
- Sauvegarde qui ne fonctionne pas
- Validation des données
- Problèmes JavaScript
- Erreurs de permissions

### 📤 [Problèmes d'Export](./export-issues)
Solutions pour les problèmes d'export :
- Export CSV/Excel qui échoue
- Données manquantes ou incorrectes
- Problèmes d'encodage
- Limite de mémoire

## Diagnostic Rapide

### 1. Vérifier l'installation

```bash
# Vérifier que le bundle est chargé
php bin/console debug:container --tag=sigmasoft_datatable

# Vérifier la configuration
php bin/console config:dump sigmasoft_data_table

# Vérifier les services disponibles
php bin/console debug:container | grep -i sigmasoft
```

### 2. Vérifier les logs

```bash
# Logs de développement
tail -f var/log/dev.log

# Chercher les erreurs spécifiques
grep -i "sigmasoft" var/log/dev.log
```

### 3. Mode debug

Activez le mode debug dans votre configuration :

```yaml title="config/packages/dev/sigmasoft_data_table.yaml"
sigmasoft_data_table:
    debug: true
    logging:
        enabled: true
        level: debug
```

## Problèmes Fréquents

### Service non trouvé

**Erreur :** `Service "Sigmasoft\DataTableBundle\Builder\DataTableBuilder" not found`

**Solution :**
```bash
composer dump-autoload
php bin/console cache:clear
```

### Template non trouvé

**Erreur :** `Unable to find template "@SigmasoftDataTable/datatable.html.twig"`

**Solution :**
1. Vérifiez que le dossier des templates est bien `templates/SigmasoftDataTable/`
2. Installez les assets : `php bin/console assets:install`

### Maker command non disponible

**Erreur :** `Command "make:datatable" is not defined`

**Solution :**
1. Installez MakerBundle : `composer require --dev symfony/maker-bundle`
2. Videz le cache : `php bin/console cache:clear`

## Obtenir de l'Aide

Si votre problème n'est pas résolu :

1. **Consultez la documentation complète**
   - [Guide d'installation](../installation)
   - [Guide de démarrage rapide](../quick-start)
   - [Documentation API](../api/overview)

2. **Recherchez dans les issues GitHub**
   - [Issues ouvertes](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
   - [Issues résolues](https://github.com/Chancel18/SigmasoftDataTableBundle/issues?q=is%3Aissue+is%3Aclosed)

3. **Créez une nouvelle issue**
   - Utilisez le template d'issue
   - Incluez les informations de diagnostic
   - Fournissez un exemple reproductible

4. **Contactez le support**
   - Email : support@sigmasoft-solution.com
   - GitHub Discussions : [Discussions](https://github.com/Chancel18/SigmasoftDataTableBundle/discussions)

## Contribuer

Vous avez trouvé une solution à un problème non documenté ? 
Contribuez à la documentation en soumettant une Pull Request !

---

*Dernière mise à jour : 31/07/2025*