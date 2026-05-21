<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class BookController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/books",
     *     tags={"Books"},
     *     summary="Liste des livres",
     *     description="Récupère la liste paginée de tous les livres.",
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string", default="application/json")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès : Liste des livres",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Livre Test"),
     *                 @OA\Property(property="author", type="string", example="Auteur Test"),
     *                 @OA\Property(property="summary", type="string", example="Description de test pour ce livre."),
     *                 @OA\Property(property="isbn", type="string", example="1234567890123")
     *             )),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return BookResource::collection(Book::paginate(2));
    }

    /**
     * @OA\Post(
     *     path="/api/books",
     *     tags={"Books"},
     *     summary="Créer un livre",
     *     description="Ajoute un nouveau livre à la base de données.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string", default="application/json")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "author", "summary", "isbn"},
     *             @OA\Property(property="title", type="string", example="Livre Test"),
     *             @OA\Property(property="author", type="string", example="Auteur Test"),
     *             @OA\Property(property="summary", type="string", example="Description de test pour ce livre."),
     *             @OA\Property(property="isbn", type="string", example="1234567890123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Succès : Livre créé",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Livre Test"),
     *                 @OA\Property(property="author", type="string", example="Auteur Test"),
     *                 @OA\Property(property="summary", type="string", example="Description de test pour ce livre."),
     *                 @OA\Property(property="isbn", type="string", example="1234567890123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Erreur : Non autorisé (Token manquant ou invalide)",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur : Données invalides (Validation)"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'author' => ['required', 'string', 'min:3', 'max:100'],
            'summary' => ['required', 'string', 'min:10', 'max:500'],
            'isbn' => ['required', 'string', 'size:13', 'unique:books,isbn'],
        ]);

        $book = Book::create($data);

        return new BookResource($book);
    }

    /**
     * @OA\Get(
     *     path="/api/books/{book}",
     *     tags={"Books"},
     *     summary="Afficher un livre",
     *     description="Récupère les informations d'un livre spécifique.",
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string", default="application/json")
     *     ),
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         required=true,
     *         description="ID du livre",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès : Informations du livre",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Livre Test"),
     *                 @OA\Property(property="author", type="string", example="Auteur Test"),
     *                 @OA\Property(property="summary", type="string", example="Description de test pour ce livre."),
     *                 @OA\Property(property="isbn", type="string", example="1234567890123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Erreur : Livre non trouvé",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Not found"))
     *     )
     * )
     */
    public function show(Book $book)
    {
        $book = Cache::remember('book-' . $book->id, 60, function () use (
            $book,
        ) {
            return $book;
        });

        return new BookResource($book);
    }

    /**
     * @OA\Put(
     *     path="/api/books/{book}",
     *     tags={"Books"},
     *     summary="Mettre à jour un livre",
     *     description="Met à jour les informations d'un livre existant.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string", default="application/json")
     *     ),
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         required=true,
     *         description="ID du livre",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Livre Test"),
     *             @OA\Property(property="author", type="string", example="Auteur Test"),
     *             @OA\Property(property="summary", type="string", example="Description de test pour ce livre."),
     *             @OA\Property(property="isbn", type="string", example="1234567890123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès : Livre mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Livre Test"),
     *                 @OA\Property(property="author", type="string", example="Auteur Test"),
     *                 @OA\Property(property="summary", type="string", example="Description de test pour ce livre."),
     *                 @OA\Property(property="isbn", type="string", example="1234567890123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Erreur : Non autorisé (Token manquant ou invalide)",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Erreur : Livre non trouvé",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Not found"))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur : Données invalides (Validation)"
     *     )
     * )
     */
    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'min:3', 'max:100'],
            'summary' => [
                'sometimes',
                'required',
                'string',
                'min:10',
                'max:500',
            ],
            'isbn' => [
                'sometimes',
                'required',
                'string',
                'size:13',
                Rule::unique('books', 'isbn')->ignore($book->id),
            ],
        ]);

        $book->update($data);
        Cache::forget('book-' . $book->id);

        return new BookResource($book);
    }

    /**
     * @OA\Delete(
     *     path="/api/books/{book}",
     *     tags={"Books"},
     *     summary="Supprimer un livre",
     *     description="Supprime un livre de la base de données.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string", default="application/json")
     *     ),
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         required=true,
     *         description="ID du livre",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Succès : Livre supprimé (Aucun contenu retourné)"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Erreur : Non autorisé (Token manquant ou invalide)",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Erreur : Livre non trouvé",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Not found"))
     *     )
     * )
     */
    public function destroy(Book $book)
    {
        Cache::forget('book-' . $book->id);
        $book->delete();

        return response()->noContent();
    }
}
