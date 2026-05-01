<script setup>
const profile = {
  name: "Georgi Zlatev",
  location: "Bulgaria",
  expertise:
    "Management Skills, Negotiation, Critical Thinking, Communication Skills",
  portfolio: {
    github: "https://github.com/GeorgiZlatev",
    linkedin: "https://linkedin.com/in/GeorgiZlatev",
  },
};

const experience = [
  {
    role: "Full Stack Web Developer",
    company: "MTG Dolphin",
    period: "January 2024 - present",
    details:
      "OOP PHP, Yii2, JavaScript, MySQL, PWA, Git, WSL 2. Building an internal app for employees: HR workflows, surveys, pay slips, documents, leave requests, and task management.",
  },
  {
    role: "Customer Support",
    company: "Entegra Ltd.",
    period: "March 2023 - December 2023",
    details: "ERP software support, documentation writing, and work with Jira.",
  },
  {
    role: "Technical Support Electronics",
    company: "NSYS Ltd.",
    period: "2017 - March 2023",
    details:
      "Assembly, setup, and maintenance of kiosks. Work with 485 communication controller and PC connection.",
  },
  {
    role: "Technical Support Electronics",
    company: "Flysystem Ltd.",
    period: "2014 - 2016",
    details: "",
  },
  {
    role: "Technical Support Electronics",
    company: "Sintex-Group Ltd.",
    period: "2012 - 2013",
    details: "",
  },
];

const education = [
  {
    school:
      'Professional High School of Mechanics, Electronics and Transport "Hristo Botev", Shumen, Bulgaria',
    specialty: "Electrical Technology",
    period: "2007 - 2011",
  },
  {
    school: "Technical University of Varna",
    specialty: "Electrical Technology",
    period: "2012 - 2016",
  },
];

const skills =
  "HTML5, CSS3, JavaScript, PHP, Bootstrap, Cordova (analogue of Flutter), SQL, Yii2, AI Academy";
</script>

<template>
  <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    <header
      class="rounded-2xl border border-gray-800 bg-gradient-to-r from-gray-900 to-gray-950 p-6 sm:p-8"
    >
      <p class="text-primary-400 text-sm font-medium tracking-wide uppercase">
        Curriculum Vitae
      </p>
      <h1 class="mt-2 text-3xl sm:text-4xl font-bold text-white">
        {{ profile.name }}
      </h1>
      <p class="mt-3 text-gray-300">{{ profile.location }}</p>
      <p class="mt-4 text-gray-300 leading-relaxed">{{ profile.expertise }}</p>
      <div class="mt-5 flex flex-wrap gap-3">
        <a
          :href="profile.portfolio.github"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center rounded-md border border-gray-700 px-4 py-2 text-sm text-gray-200 hover:bg-gray-800 transition-colors"
        >
          GitHub
        </a>
        <a
          :href="profile.portfolio.linkedin"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center rounded-md border border-gray-700 px-4 py-2 text-sm text-gray-200 hover:bg-gray-800 transition-colors"
        >
          LinkedIn
        </a>
      </div>
    </header>

    <div class="mt-8 grid gap-8 lg:grid-cols-3">
      <section
        class="lg:col-span-2 rounded-2xl border border-gray-800 bg-gray-950/70 p-6"
      >
        <h2 class="text-xl font-semibold text-white">Experience</h2>
        <div class="mt-5 space-y-6">
          <article
            v-for="item in experience"
            :key="`${item.role}-${item.company}-${item.period}`"
            class="border-b border-gray-800 pb-5 last:border-b-0 last:pb-0"
          >
            <h3 class="text-lg font-semibold text-white">{{ item.role }}</h3>
            <p class="text-primary-400 mt-1">{{ item.company }}</p>
            <p class="text-sm text-gray-400 mt-1">{{ item.period }}</p>
            <p v-if="item.details" class="text-gray-300 mt-3 leading-relaxed">
              {{ item.details }}
            </p>
          </article>
        </div>
      </section>

      <aside class="space-y-8">
        <section class="rounded-2xl border border-gray-800 bg-gray-950/70 p-6">
          <h2 class="text-xl font-semibold text-white">Education</h2>
          <div class="mt-5 space-y-5">
            <article v-for="item in education" :key="item.school">
              <h3 class="text-white font-medium">{{ item.school }}</h3>
              <p class="text-gray-300 mt-1">{{ item.specialty }}</p>
              <p class="text-sm text-gray-400 mt-1">{{ item.period }}</p>
            </article>
          </div>
        </section>

        <section class="rounded-2xl border border-gray-800 bg-gray-950/70 p-6">
          <h2 class="text-xl font-semibold text-white">Skills</h2>
          <p class="mt-4 text-gray-300 leading-relaxed">{{ skills }}</p>
          <p class="mt-4 text-gray-300">
            <span class="text-white font-medium">Language:</span> English
            (Intermediate)
          </p>
        </section>
      </aside>
    </div>
  </section>
</template>
