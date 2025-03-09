# Projet de Développement d'Applications

## Sommaire
- [Projet de Développement d'Applications](#projet-de-développement-dapplications)
  - [Architecture du Projet](#architecture-du-projet)
    - [Technologies utilisées](#technologies-utilisées)
  
---

## Membres de l'équipe
- **Développeur 1** :
- **Développeur 2** : 
- **Développeur 3** : 
- **Développeur 4** :


## Installation et lancement




## Tests
Voici les étapes à suivre :

1. **Installation de composer:**

Télécharger Composer :
Allez sur le site officiel de Composer [getcomposer.org]( https://getcomposer.org/)

2. **Ajout de composer au projet**
   ```bash
   composer install
   ```
3. **Execution des tests:**
   ```bash
   ./vendor/bin/phpunit
   ```
    3.a. **Lancement d'un seul fichier**
    ```bash
    ./vendor/bin/phpunit --filter [nomfichier]
    ```


## Coverage

1. **Installation de Xdebug:**

   1.1 **Sous  windows**
   ```bash
    php -i
   ```
   - Copiez la sortie de cette commande ici: https://xdebug.org/wizard

   - Suivez les instructions du site

   - Ajoutez ceci à **php.ini**
      ```ini
      [Xdebug]
      zend_extension=php_xdebug.dll
      xdebug.mode=coverage
      ```
 
   1.2 **Sous  mac/linux**
      ```bash
      pecl install xdebug
      ```

   - Ajoutez ceci à **php.ini**
      ```ini
      [Xdebug]
      zend_extension=xdebug.so
      xdebug.mode=coverage
      ```


4. **Génération du coverage**
   ```bash
   ./vendor/bin/phpunit --coverage-html coverage
   ```

Cela générera un rapport de couverture dans le répertoire coverage. Ouvrez le fichier **index.html** dans un navigateur pour voir le rapport.

