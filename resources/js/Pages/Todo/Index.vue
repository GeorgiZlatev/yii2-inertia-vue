<script setup>
import { computed, ref } from "vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
  isAdmin: {
    type: Boolean,
    required: true,
  },
  todos: {
    type: Array,
    required: true,
  },
});

const page = usePage();

const createForm = useForm({
  "Todo[title]": "",
  "Todo[description]": "",
  "Todo[is_completed]": false,
});

const editingId = ref(null);
const editForm = useForm({
  "Todo[title]": "",
  "Todo[description]": "",
  "Todo[is_completed]": false,
});
const deleteForm = useForm({});
const deleteModal = ref({
  open: false,
  todo: null,
});

const sortedTodos = computed(() =>
  [...props.todos].sort((a, b) => Number(b.createdAt) - Number(a.createdAt)),
);

const formatDate = (timestamp) => {
  if (!timestamp) {
    return "-";
  }

  const date = new Date(Number(timestamp) * 1000);

  return new Intl.DateTimeFormat("bg-BG", {
    dateStyle: "short",
    timeStyle: "short",
  }).format(date);
};

const createTask = () => {
  createForm.post("/todo/create", {
    preserveScroll: true,
    onSuccess: () => {
      createForm.reset();
      createForm["Todo[is_completed]"] = false;
    },
  });
};

const startEdit = (todo) => {
  if (!todo?.canEdit) {
    return;
  }

  editingId.value = todo.id;
  editForm["Todo[title]"] = todo.title;
  editForm["Todo[description]"] = todo.description;
  editForm["Todo[is_completed]"] = Boolean(todo.isCompleted);
};

const cancelEdit = () => {
  editingId.value = null;
  editForm.reset();
  editForm["Todo[is_completed]"] = false;
};

const updateTask = (id) => {
  editForm.post(`/todo/update?id=${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      cancelEdit();
    },
  });
};

const openDeleteModal = (todo) => {
  if (!todo?.canDelete) {
    return;
  }

  deleteModal.value = {
    open: true,
    todo,
  };
};

const closeDeleteModal = () => {
  deleteModal.value = {
    open: false,
    todo: null,
  };
};

const confirmDeleteTask = () => {
  const todo = deleteModal.value.todo;

  if (!todo?.id) {
    closeDeleteModal();
    return;
  }

  deleteForm.post(`/todo/delete?id=${todo.id}`, {
    preserveScroll: true,
    onFinish: () => {
      closeDeleteModal();
    },
  });
};

const completeTask = (todo) => {
  if (todo.isCompleted || !todo.canComplete) {
    return;
  }

  router.post(
    `/todo/complete?id=${todo.id}`,
    {},
    {
      preserveScroll: true,
      preserveState: true,
    },
  );
};

const completeAllTasks = () => {
  if (sortedTodos.value.length === 0) {
    return;
  }

  const confirmed = window.confirm(
    props.isAdmin
      ? "Да маркирам ли всички задачи като приключени?"
      : "Да маркирам ли всички ваши задачи като приключени?",
  );

  if (!confirmed) {
    return;
  }

  router.post(
    "/todo/complete-all",
    {},
    {
      preserveScroll: true,
      preserveState: true,
    },
  );
};

const fieldError = (field) => {
  const error = page.props.errors?.[field];

  if (Array.isArray(error)) {
    return error[0] ?? null;
  }

  return typeof error === "string" ? error : null;
};
</script>

<template>
  <Head title="To-Do List" />

  <section class="py-6 sm:py-10">
    <div class="mx-auto w-full max-w-6xl space-y-6">
      <header
        class="rounded-2xl border border-gray-800 bg-gradient-to-r from-gray-900 to-gray-950 p-6 sm:p-8"
      >
        <p class="text-sm uppercase tracking-wide text-primary-400">Task Board</p>
        <h1 class="mt-2 text-3xl font-bold text-white sm:text-4xl">To-Do List</h1>
        <p class="mt-3 text-gray-300">
          {{
            isAdmin
              ? "Админ: виждаш и управляваш всички задачи."
              : "Виждаш и управляваш само своите задачи."
          }}
        </p>
      </header>

      <section class="rounded-2xl border border-gray-800 bg-gray-950/70 p-5 sm:p-6">
        <h2 class="text-xl font-semibold text-white">Нова задача</h2>

        <form class="mt-4 grid gap-4" @submit.prevent="createTask">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-200" for="todo-title"
              >Title</label
            >
            <input
              id="todo-title"
              v-model="createForm['Todo[title]']"
              type="text"
              class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-white outline-none transition focus:border-primary-500"
              placeholder="Пример: Подготви месечен отчет"
            />
            <p v-if="fieldError('title')" class="mt-1 text-sm text-red-400">
              {{ fieldError("title") }}
            </p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-gray-200" for="todo-description"
              >Description</label
            >
            <textarea
              id="todo-description"
              v-model="createForm['Todo[description]']"
              rows="4"
              class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-white outline-none transition focus:border-primary-500"
              placeholder="Опиши задачата..."
            />
            <p v-if="fieldError('description')" class="mt-1 text-sm text-red-400">
              {{ fieldError("description") }}
            </p>
          </div>

          <label class="inline-flex w-fit items-center gap-2 text-sm text-gray-200">
            <input
              v-model="createForm['Todo[is_completed]']"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-600 bg-gray-900 text-primary-600"
            />
            Приключена
          </label>

          <button
            type="submit"
            class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-2 font-medium text-white transition hover:bg-primary-700 sm:w-fit"
            :disabled="createForm.processing"
          >
            Създай задача
          </button>
        </form>
      </section>

      <section class="rounded-2xl border border-gray-800 bg-gray-950/70 p-5 sm:p-6">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-white">Задачи</h2>
          <div class="flex items-center gap-3">
            <span class="text-sm text-gray-400">Общо: {{ sortedTodos.length }}</span>
            <button
              type="button"
              class="rounded-lg border border-emerald-500 px-3 py-1.5 text-xs font-medium text-emerald-300 transition hover:bg-emerald-900/30 disabled:cursor-not-allowed disabled:border-gray-700 disabled:text-gray-500"
              :disabled="sortedTodos.length === 0"
              @click="completeAllTasks"
            >
              {{ isAdmin ? "Приключи всички" : "Приключи моите" }}
            </button>
          </div>
        </div>

        <div v-if="sortedTodos.length === 0" class="mt-4 rounded-lg border border-gray-800 p-4 text-gray-300">
          Все още няма задачи.
        </div>

        <div v-else class="mt-4 space-y-4 md:hidden">
          <article
            v-for="todo in sortedTodos"
            :key="todo.id"
            class="rounded-xl border border-gray-800 bg-gray-900/70 p-4"
          >
            <template v-if="editingId === todo.id">
              <form class="space-y-3" @submit.prevent="updateTask(todo.id)">
                <input
                  v-model="editForm['Todo[title]']"
                  type="text"
                  class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-white"
                />
                <textarea
                  v-model="editForm['Todo[description]']"
                  rows="3"
                  class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-white"
                />
                <label class="inline-flex items-center gap-2 text-sm text-gray-200">
                  <input
                    v-model="editForm['Todo[is_completed]']"
                    type="checkbox"
                    class="h-4 w-4"
                    :disabled="!isAdmin"
                  />
                  Приключена
                </label>
                <div class="flex gap-2">
                  <button type="submit" class="rounded-lg bg-primary-600 px-3 py-2 text-sm text-white">
                    Запази
                  </button>
                  <button
                    type="button"
                    class="rounded-lg border border-gray-600 px-3 py-2 text-sm text-gray-200"
                    @click="cancelEdit"
                  >
                    Отказ
                  </button>
                </div>
              </form>
            </template>

            <template v-else>
              <h3 class="text-lg font-semibold text-white">{{ todo.title }}</h3>
              <p class="mt-2 whitespace-pre-line text-sm text-gray-300">{{ todo.description }}</p>
              <dl class="mt-3 space-y-1 text-sm text-gray-400">
                <div>
                  <dt class="inline text-gray-500">Създадена:</dt>
                  <dd class="inline"> {{ formatDate(todo.createdAt) }}</dd>
                </div>
                <div>
                  <dt class="inline text-gray-500">Приключена:</dt>
                  <dd class="inline"> {{ todo.isCompleted ? "Да" : "Не" }}</dd>
                </div>
                <div v-if="isAdmin">
                  <dt class="inline text-gray-500">От:</dt>
                  <dd class="inline"> {{ todo.createdBy?.username || "-" }}</dd>
                </div>
              </dl>

              <div class="mt-4 flex flex-wrap gap-2">
                <button
                  type="button"
                  class="rounded-lg border border-emerald-500 px-3 py-2 text-sm text-emerald-300 disabled:cursor-not-allowed disabled:border-gray-700 disabled:text-gray-500"
                  :disabled="todo.isCompleted || !todo.canComplete"
                  @click="completeTask(todo)"
                >
                  Приключи
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-primary-600 px-3 py-2 text-sm text-white disabled:cursor-not-allowed disabled:bg-gray-700"
                  :disabled="!todo.canEdit"
                  @click="startEdit(todo)"
                >
                  Редакция
                </button>
                <button
                  type="button"
                  class="rounded-lg border border-red-500 px-3 py-2 text-sm text-red-400 disabled:cursor-not-allowed disabled:border-gray-700 disabled:text-gray-500"
                  :disabled="!todo.canDelete"
                  @click="openDeleteModal(todo)"
                >
                  Изтрий
                </button>
              </div>
            </template>
          </article>
        </div>

        <div
          v-if="sortedTodos.length > 0"
          class="mt-4 hidden overflow-x-auto md:block"
        >
          <table class="w-full min-w-[820px] text-left text-sm">
            <thead>
              <tr class="border-b border-gray-800 text-gray-400">
                <th class="px-3 py-2">Title</th>
                <th class="px-3 py-2">Description</th>
                <th class="px-3 py-2">Създадена</th>
                <th class="px-3 py-2">Приключена</th>
                <th v-if="isAdmin" class="px-3 py-2">От кого</th>
                <th class="px-3 py-2">Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="todo in sortedTodos" :key="todo.id" class="border-b border-gray-900 align-top">
                <template v-if="editingId === todo.id">
                  <td class="px-3 py-3">
                    <input v-model="editForm['Todo[title]']" type="text" class="w-full rounded border border-gray-700 bg-gray-900 px-2 py-1 text-white" />
                  </td>
                  <td class="px-3 py-3">
                    <textarea v-model="editForm['Todo[description]']" rows="3" class="w-full rounded border border-gray-700 bg-gray-900 px-2 py-1 text-white" />
                  </td>
                  <td class="px-3 py-3 text-gray-300">{{ formatDate(todo.createdAt) }}</td>
                  <td class="px-3 py-3">
                    <label class="inline-flex items-center gap-2 text-gray-200">
                      <input
                        v-model="editForm['Todo[is_completed]']"
                        type="checkbox"
                        class="h-4 w-4"
                        :disabled="!isAdmin"
                      />
                      Да
                    </label>
                  </td>
                  <td v-if="isAdmin" class="px-3 py-3 text-gray-300">{{ todo.createdBy?.username || "-" }}</td>
                  <td class="px-3 py-3">
                    <div class="flex flex-wrap gap-2">
                      <button type="button" class="rounded bg-primary-600 px-2 py-1 text-white" @click="updateTask(todo.id)">
                        Запази
                      </button>
                      <button type="button" class="rounded border border-gray-600 px-2 py-1 text-gray-200" @click="cancelEdit">
                        Отказ
                      </button>
                    </div>
                  </td>
                </template>

                <template v-else>
                  <td class="px-3 py-3 font-medium text-white">{{ todo.title }}</td>
                  <td class="px-3 py-3 text-gray-300">{{ todo.description }}</td>
                  <td class="px-3 py-3 text-gray-300">{{ formatDate(todo.createdAt) }}</td>
                  <td class="px-3 py-3 text-gray-300">{{ todo.isCompleted ? "Да" : "Не" }}</td>
                  <td v-if="isAdmin" class="px-3 py-3 text-gray-300">{{ todo.createdBy?.username || "-" }}</td>
                  <td class="px-3 py-3">
                    <div class="flex flex-wrap gap-2">
                      <button
                        type="button"
                        class="rounded border border-emerald-500 px-2 py-1 text-emerald-300 disabled:cursor-not-allowed disabled:border-gray-700 disabled:text-gray-500"
                        :disabled="todo.isCompleted || !todo.canComplete"
                        @click="completeTask(todo)"
                      >
                        Приключи
                      </button>
                      <button
                        type="button"
                        class="rounded bg-primary-600 px-2 py-1 text-white disabled:cursor-not-allowed disabled:bg-gray-700"
                        :disabled="!todo.canEdit"
                        @click="startEdit(todo)"
                      >
                        Редакция
                      </button>
                      <button
                        type="button"
                        class="rounded border border-red-500 px-2 py-1 text-red-400 disabled:cursor-not-allowed disabled:border-gray-700 disabled:text-gray-500"
                        :disabled="!todo.canDelete"
                        @click="openDeleteModal(todo)"
                      >
                        Изтрий
                      </button>
                    </div>
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </section>

  <div
    v-if="deleteModal.open"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="delete-task-title"
  >
    <div class="w-full max-w-md rounded-2xl border border-gray-700 bg-gray-900 p-5 shadow-2xl">
      <h3 id="delete-task-title" class="text-lg font-semibold text-white">Потвърждение за изтриване</h3>
      <p class="mt-2 text-sm text-gray-300">
        Сигурни ли сте, че искате да изтриете задачата
        <span class="font-medium text-white">
          {{ deleteModal.todo?.title ? `"${deleteModal.todo.title}"` : "" }}
        </span>
        ?
      </p>
      <div class="mt-5 flex justify-end gap-2">
        <button
          type="button"
          class="rounded-lg border border-gray-600 px-4 py-2 text-sm text-gray-200"
          @click="closeDeleteModal"
        >
          Отказ
        </button>
        <button
          type="button"
          class="rounded-lg border border-red-500 bg-red-600/20 px-4 py-2 text-sm font-medium text-red-300 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="deleteForm.processing"
          @click="confirmDeleteTask"
        >
          Изтрий
        </button>
      </div>
    </div>
  </div>
</template>
