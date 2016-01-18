# Redimlive

Ahhhh, ce bon vieux redimlive.php... Certains le regrettent ! Ce module fait à peu près la même chose : 
récupérer une image de produit, contenu, dossier ou rubrique, la redimensionner, et la renvoyer comme une image,
de telle sorte que vous puissiez faire dans vos pages statiques (hors Thelia) quelque chose comme :

```<img src="http://www.maboutique.com/redilive/produit/231/1/640/480">```

pour récupérer l'image numéro 1 du produit 231 avec une dimensions de 640 x 480

C'est tout !

L'URL est donc de la forme :

http://www.maboutique.com/redilive/type/id/num/width/height[/resize_mode]

- type: product ou category ou folder ou content ou brand
- id : l'ID de l'objet 
- num : le numero de l'image à récupérer, 1 est la 1ere image
- width: la largeur désirée, en pixel
- height: la hauteur désirée, en pixels
- resize_mode (optionnel): le mode de redimensionnement: crop ou borders ou none (defaut: none)

Si l'image n'est pas trouvée, vous recevrez un code erreur 404, et pas dimage.

Si un truc se passe mal pendant le redimensionnement, vous recevrez une erreur 500, et toujours pas d'image.