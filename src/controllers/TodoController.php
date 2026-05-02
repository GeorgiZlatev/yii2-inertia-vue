<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Todo;
use Throwable;
use Yii;
use yii\filters\{AccessControl, VerbFilter};
use yii\inertia\web\Controller;
use yii\web\{ForbiddenHttpException, NotFoundHttpException, Response};

/**
 * Handles authenticated to-do list actions.
 */
final class TodoController extends Controller
{
    public function actionComplete(int $id): Response
    {
        $todo = $this->findTodo($id);

        if (!$this->canComplete($todo)) {
            throw new ForbiddenHttpException('Нямате достъп до тази задача.');
        }

        if ((bool) $todo->is_completed) {
            Yii::$app->session->setFlash('info', 'Задачата вече е приключена.');

            return $this->redirect(['todo/index']);
        }

        $now = time();
        $todo->is_completed = 1;
        $todo->completed_at = $now;
        $todo->updated_at = $now;

        $saved = $todo->save(false, ['is_completed', 'completed_at', 'updated_at']);

        Yii::$app->session->setFlash(
            $saved ? 'success' : 'error',
            $saved ? 'Задачата е маркирана като приключена.' : 'Неуспешно приключване на задача.',
        );

        return $this->redirect(['todo/index']);
    }

    public function actionCompleteAll(): Response
    {
        $user = Yii::$app->user;
        $isAdmin = $user->can('admin');
        $now = time();

        $condition = ['is_completed' => 0];

        if (!$isAdmin) {
            $condition = [
                'and',
                ['is_completed' => 0],
                ['created_by' => (int) $user->id],
            ];
        }

        $updated = Todo::updateAll(
            [
                'is_completed' => 1,
                'completed_at' => $now,
                'updated_at' => $now,
            ],
            $condition,
        );

        if ($updated > 0) {
            Yii::$app->session->setFlash(
                'success',
                $isAdmin
                    ? 'Всички задачи са маркирани като приключени.'
                    : 'Всички ваши задачи са маркирани като приключени.',
            );
        } else {
            Yii::$app->session->setFlash('info', 'Няма неприключени задачи за обновяване.');
        }

        return $this->redirect(['todo/index']);
    }
    public function actionCreate(): Response
    {
        $todo = new Todo();

        /** @var array<string, mixed> $post */
        $post = $this->request->post();

        if ($todo->load($post)) {
            $todo->created_by = (int) Yii::$app->user->id;
            $todo->is_completed = (int) $todo->is_completed;
            $todo->completed_at = $todo->is_completed ? time() : null;

            try {
                $saved = $todo->save();
            } catch (Throwable $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $saved = false;
            }

            if ($saved) {
                Yii::$app->session->setFlash('success', 'Задачата беше създадена успешно.');
            } elseif ($todo->hasErrors()) {
                Yii::$app->session->setFlash('errors', $todo->getErrors());
            } else {
                Yii::$app->session->setFlash('error', 'Неуспешно създаване на задача.');
            }
        }

        return $this->redirect(['todo/index']);
    }

    public function actionDelete(int $id): Response
    {
        $todo = $this->findTodo($id);

        if (!$this->canEditOrDelete($todo)) {
            throw new ForbiddenHttpException('Само админ може да изтрива приключени задачи.');
        }

        try {
            $deleted = $todo->delete() !== false;
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            $deleted = false;
        }

        Yii::$app->session->setFlash(
            $deleted ? 'success' : 'error',
            $deleted ? 'Задачата беше изтрита.' : 'Неуспешно изтриване на задача.',
        );

        return $this->redirect(['todo/index']);
    }

    public function actionIndex(): Response
    {
        $user = Yii::$app->user;
        $isAdmin = $user->can('admin');
        $currentUserId = (int) $user->id;

        $query = Todo::find()->with('creator')->orderBy(['created_at' => SORT_DESC]);

        if (!$isAdmin) {
            $query->andWhere(['created_by' => (int) $user->id]);
        }

        /** @var Todo[] $models */
        $models = $query->all();

        $todos = array_map(
            static function (Todo $todo) use ($isAdmin, $currentUserId): array {
                $isOwner = (int) $todo->created_by === $currentUserId;
                $canComplete = $isAdmin || $isOwner;
                $canEditOrDelete = $isAdmin || ($isOwner && !(bool) $todo->is_completed);

                return [
                'id' => $todo->id,
                'title' => $todo->title,
                'description' => $todo->description,
                'isCompleted' => (bool) $todo->is_completed,
                'createdAt' => $todo->created_at,
                'completedAt' => $todo->completed_at,
                'canComplete' => $canComplete,
                'canEdit' => $canEditOrDelete,
                'canDelete' => $canEditOrDelete,
                'createdBy' => [
                    'id' => $todo->creator?->id,
                    'username' => $todo->creator?->username,
                    'email' => $todo->creator?->email,
                ],
                ];
            },
            $models,
        );

        return $this->inertia(
            'Todo/Index',
            [
                'isAdmin' => $isAdmin,
                'todos' => $todos,
            ],
        );
    }

    public function actionUpdate(int $id): Response
    {
        $todo = $this->findTodo($id);

        if (!$this->canEditOrDelete($todo)) {
            throw new ForbiddenHttpException('Само админ може да редактира приключени задачи.');
        }

        /** @var array<string, mixed> $post */
        $post = $this->request->post();
        $isAdmin = Yii::$app->user->can('admin');
        $originalIsCompleted = (int) $todo->is_completed;
        $originalCompletedAt = $todo->completed_at;

        if ($todo->load($post)) {
            if ($isAdmin) {
                $todo->is_completed = (int) $todo->is_completed;
                $todo->completed_at = $todo->is_completed ? ($todo->completed_at ?? time()) : null;
            } else {
                $todo->is_completed = $originalIsCompleted;
                $todo->completed_at = $originalCompletedAt;
            }

            try {
                $saved = $todo->save();
            } catch (Throwable $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $saved = false;
            }

            if ($saved) {
                Yii::$app->session->setFlash('success', 'Задачата беше обновена успешно.');
            } elseif ($todo->hasErrors()) {
                Yii::$app->session->setFlash('errors', $todo->getErrors());
            } else {
                Yii::$app->session->setFlash('error', 'Неуспешно обновяване на задача.');
            }
        }

        return $this->redirect(['todo/index']);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create', 'update', 'delete', 'complete-all', 'complete'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete', 'complete-all', 'complete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                    'create' => ['post'],
                    'update' => ['post'],
                    'delete' => ['post'],
                    'complete-all' => ['post'],
                    'complete' => ['post'],
                ],
            ],
        ];
    }

    private function canComplete(Todo $todo): bool
    {
        $user = Yii::$app->user;

        return $user->can('admin') || (int) $todo->created_by === (int) $user->id;
    }

    private function canEditOrDelete(Todo $todo): bool
    {
        if (Yii::$app->user->can('admin')) {
            return true;
        }

        return (int) $todo->created_by === (int) Yii::$app->user->id && !(bool) $todo->is_completed;
    }

    private function findTodo(int $id): Todo
    {
        $todo = Todo::findOne($id);

        if (!$todo instanceof Todo) {
            throw new NotFoundHttpException('Задачата не е намерена.');
        }

        return $todo;
    }
}
