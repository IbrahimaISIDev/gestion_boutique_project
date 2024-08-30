<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Traits\RestResponseTrait;
use App\Enums\StateEnum;

class ArticleController extends Controller
{
    use RestResponseTrait;

    public function index()
    {
        $articles = Article::all();
        return $this->sendResponse($articles, StateEnum::SUCCESS, 'Articles récupérés avec succès');
    }

    public function store(StoreArticleRequest $request)
    {
        $validatedData = $request->validated();

        if (empty($validatedData)) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Au moins un article est requis', 422);
        }

        $article = Article::create($validatedData);

        return $this->sendResponse($article, StateEnum::SUCCESS, 'Article créé avec succès', 201);
    }

    public function show(Article $article)
    {
        return $this->sendResponse($article, StateEnum::SUCCESS, 'Article récupéré avec succès');
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $validatedData = $request->validated();

        if (empty($validatedData)) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Au moins un champ d\'article est requis pour la mise à jour', 422);
        }

        if (isset($validatedData['stock'])) {
            $validatedData['stock'] = $article->stock + $validatedData['stock'];
        }

        $article->update($validatedData);

        return $this->sendResponse($article, StateEnum::SUCCESS, 'Article mis à jour avec succès');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return $this->sendResponse(null, StateEnum::SUCCESS, 'Article supprimé avec succès');
    }

    public function trashed()
    {
        $trashedArticles = Article::onlyTrashed()->get();
        return $this->sendResponse($trashedArticles, StateEnum::SUCCESS, 'Articles supprimés récupérés avec succès');
    }

    public function restore($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        if (!$article->trashed()) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Cet article n\'est pas dans la corbeille', 400);
        }
        $article->restore();
        return $this->sendResponse($article, StateEnum::SUCCESS, 'Article restauré avec succès');
    }

    public function forceDelete($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        if (!$article->trashed()) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Vous ne pouvez pas supprimer définitivement un article qui n\'est pas dans la corbeille', 400);
        }
        $article->forceDelete();
        return $this->sendResponse(null, StateEnum::SUCCESS, 'Article supprimé définitivement');
    }

    public function updateMultiple(Request $request)
    {
        $articlesToUpdate = $request->articles;

        if (empty($articlesToUpdate)) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Au moins un article est requis pour la mise à jour multiple', 422);
        }

        $updatedArticles = [];
        $failedUpdates = [];

        DB::beginTransaction();

        try {
            foreach ($articlesToUpdate as $articleData) {
                try {
                    if (!isset($articleData['id'])) {
                        throw new \Exception("L'ID de l'article est manquant");
                    }

                    $article = Article::find($articleData['id']);
                    if (!$article) {
                        throw new \Exception("Article avec l'ID {$articleData['id']} introuvable");
                    }

                    $updateRequest = new UpdateArticleRequest();
                    $updateRequest->replace($articleData);
                    $validatedData = $updateRequest->validate($updateRequest->rules());

                    if (empty($validatedData)) {
                        throw new \Exception("Au moins un champ d'article est requis pour la mise à jour");
                    }

                    if (isset($validatedData['stock'])) {
                        $newStock = $article->stock + $validatedData['stock'];
                        if ($newStock < 0) {
                            throw new \Exception("Le stock ne peut pas être négatif");
                        }
                        $validatedData['stock'] = $newStock;
                    }

                    // Mise à jour de l'article valide
                    $article->fill($validatedData);
                    $updatedArticles[] = $article;
                } catch (\Exception $e) {
                    // Enregistrer l'échec de mise à jour
                    $failedUpdates[] = [
                        'article_data' => $articleData,
                        'error_message' => $e->getMessage()
                    ];
                }
            }

            // Enregistrer tous les articles valides
            foreach ($updatedArticles as $article) {
                $article->save();
            }

            DB::commit();

            $status = count($failedUpdates) > 0 ? StateEnum::ECHEC : StateEnum::SUCCESS;
            $message = count($failedUpdates) > 0
                ? 'Certaines mises à jour ont échoué, mais les articles valides ont été mis à jour'
                : 'Toutes les mises à jour ont réussi';
            $httpStatus = count($failedUpdates) > 0 ? 422 : 200;

            return $this->sendResponse([
                'updated_articles' => $updatedArticles,
                'failed_updates' => $failedUpdates
            ], $status, $message, $httpStatus);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendResponse(null, StateEnum::ECHEC, 'Erreur lors de la mise à jour multiple : ' . $e->getMessage(), 500);
        }
    }
}
