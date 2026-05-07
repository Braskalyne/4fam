<?php

namespace App\Controller\Admin;

use App\Entity\ArtistProfile;
use App\Entity\ArtistImage;
use App\Repository\ArtistProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/artist')]
class AdminArtistController extends AbstractController
{
    #[Route('/{slug}/edit', name: 'admin_artist_edit', requirements: ['slug' => 'maud|camille'])]
    public function edit(
        string $slug,
        Request $request,
        EntityManagerInterface $em,
        ArtistProfileRepository $artistProfileRepository,
        SluggerInterface $slugger
    ): Response {
        // Récupérer ou créer le profil
        $artist = $artistProfileRepository->findOneBy(['slug' => $slug]);
        if (!$artist) {
            $artist = new ArtistProfile();
            $artist->setSlug($slug);
            $artist->setName(ucfirst($slug));
            $em->persist($artist);
            $em->flush();
        }

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            if ($action === 'update_info') {
                $artist->setName($request->request->get('name'));
                $artist->setBio($request->request->get('bio'));
                $artist->setInstagramUrl($request->request->get('instagram_url'));
                $em->flush();
                $this->addFlash('success', 'Profil mis à jour !');
            } elseif ($action === 'add_images') {
                $uploadedFiles = $request->files->get('images', []);
                $startPosition = count($artist->getImages());

                foreach ($uploadedFiles as $index => $uploadedFile) {
                    if ($uploadedFile) {
                        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $extension = $uploadedFile->getClientOriginalExtension() ?: 'jpg';
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                        $uploadedFile->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads/gallery',
                            $newFilename
                        );

                        $image = new ArtistImage();
                        $image->setFilename($newFilename);
                        $image->setArtist($artist);
                        $image->setPosition($startPosition + $index);

                        $em->persist($image);
                    }
                }
                $em->flush();
                $this->addFlash('success', 'Images ajoutées !');
            } elseif ($action === 'update_positions') {
                $positions = $request->request->all('positions');
                foreach ($positions as $imageId => $position) {
                    $image = $em->find(ArtistImage::class, (int) $imageId);
                    if ($image && $image->getArtist() === $artist) {
                        $image->setPosition((int) $position);
                    }
                }
                $em->flush();
                $this->addFlash('success', 'Ordre des images mis à jour !');
            } elseif ($action === 'delete_image') {
                $imageId = (int) $request->request->get('image_id');
                $image = $em->find(ArtistImage::class, $imageId);
                if ($image && $image->getArtist() === $artist) {
                    $filename = $image->getFilename();
                    $em->remove($image);
                    $em->flush();

                    $filepath = $this->getParameter('kernel.project_dir') . '/public/uploads/gallery/' . $filename;
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                    $this->addFlash('success', 'Image supprimée !');
                }
            }

            return $this->redirectToRoute('admin_artist_edit', ['slug' => $slug]);
        }

        return $this->render('admin/artist/edit.html.twig', [
            'artist' => $artist,
        ]);
    }
}
