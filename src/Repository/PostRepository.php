<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    
    public function findAllPosts(){
        return $this->getEntityManager()
            ->createQuery('
                SELECT post.id, post.title, post.description, post.file, post.creation_date, post.url
                FROM App\Entity\Post post
                ORDER BY post.id DESC 
            '); //Quitamos getREsult para que el paginador sea efectivo
            //->getResult();//Traemos todos
    }
    
    
    
    // NOTA: Se realiza una consulta personalizada para traer info de la Base de datos.
    // public function findPost($id)
    // {
    //     $entityManager = $this->getEntityManager();

    //     //NOTA: Aquí podemos definir que datos queremos mostrar en pantalla
    //     $query = $entityManager->createQuery('
    //         SELECT post.id, post.title, post.type
    //         FROM App\Entity\Post post
    //         WHERE post.id =:id
    //     ')->setParameter('id', $id);
        
    //     return $query->getResult();
    // }
    

//    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
