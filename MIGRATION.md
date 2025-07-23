# 🔄 Guide de Migration - SigmasoftDataTableBundle

## ⚠️ BREAKING CHANGE - Nom du Composant Twig

### Problème Résolu
Le nom de composant `SigmasoftDataTableComponent` causait des conflits lors de l'installation. Le composant a été renommé pour éviter les collisions.

### ✅ Migration Requise

#### Ancien nom (❌ Ne fonctionne plus)
```twig
<twig:SigmasoftDataTableComponent entityClass="App\\Entity\\User" />
```

#### Nouveau nom (✅ À utiliser maintenant)
```twig
<twig:SigmasoftDataTable entityClass="App\\Entity\\User" />
```

### 🚀 Migration Automatique

Pour migrer automatiquement tous vos templates :

#### Sur Linux/macOS
```bash
# Remplacer dans tous les fichiers Twig
find . -name "*.twig" -exec sed -i 's/SigmasoftDataTableComponent/SigmasoftDataTable/g' {} +

# Remplacer dans tous les fichiers PHP
find . -name "*.php" -exec sed -i 's/SigmasoftDataTableComponent/SigmasoftDataTable/g' {} +
```

#### Sur Windows (PowerShell)
```powershell
# Remplacer dans tous les fichiers Twig
Get-ChildItem -Recurse -Include "*.twig" | ForEach-Object {
    (Get-Content $_.FullName) -replace 'SigmasoftDataTableComponent', 'SigmasoftDataTable' | Set-Content $_.FullName
}

# Remplacer dans tous les fichiers PHP
Get-ChildItem -Recurse -Include "*.php" | ForEach-Object {
    (Get-Content $_.FullName) -replace 'SigmasoftDataTableComponent', 'SigmasoftDataTable' | Set-Content $_.FullName
}
```

### 📋 Vérification Post-Migration

Après migration, vérifiez que tous les changements sont corrects :

```bash
# Vérifier qu'il ne reste plus d'anciennes références
grep -r "SigmasoftDataTableComponent" templates/
grep -r "SigmasoftDataTableComponent" src/

# Vider le cache Symfony
php bin/console cache:clear
```

### 🎯 Nouveau Nommage

| Ancienne référence | Nouvelle référence |
|-------------------|-------------------|
| `SigmasoftDataTableComponent` | `SigmasoftDataTable` |
| `data-live-component="SigmasoftDataTableComponent"` | `data-live-component="SigmasoftDataTable"` |
| `<twig:SigmasoftDataTableComponent />` | `<twig:SigmasoftDataTable />` |

### 🔧 Génération Automatique Mise à Jour

La commande Maker génère maintenant automatiquement le bon nom :

```bash
# Génération avec nouveau nom
php bin/console make:datatable User --controller --with-actions

# Génère automatiquement :
<twig:SigmasoftDataTable entityClass="App\\Entity\\User" />
```

### ✅ Avantages du Nouveau Nom

- ✅ **Plus court et plus lisible**
- ✅ **Évite les conflits de noms**
- ✅ **Installation sans erreur**
- ✅ **Compatibilité améliorée**

### 🚨 Attention

Cette migration est **obligatoire** pour les versions `1.3.1+`. L'ancien nom ne fonctionnera plus et causera des erreurs de composant non trouvé.

### 📞 Support

En cas de problème lors de la migration :
- 🐛 [GitHub Issues](https://github.com/Chancel18/SigmasoftDataTableBundle/issues)
- 📖 [Documentation](README.md)
- 💬 [Discussions](https://github.com/Chancel18/SigmasoftDataTableBundle/discussions)