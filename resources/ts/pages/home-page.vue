<script setup lang="ts">
import { VCard, VCardText } from 'vuetify/components';

</script>

<template>
  <VCard class="welcome-page">
    <div class="welcome-card">
      <!-- <img src="/images/svg/undraw_team_spirit.svg" alt="Motivasyon" class="welcome-image" /> -->
      <h1 class="welcome-title">OFT MES'e Hoşgeldiniz!</h1>
      <br>
      <br>
      <p class="welcome-desc">
        Üretim süreçlerinizi dijitalleştirmenin ve verimliliği artırmanın tam zamanı!<br> <br>
        Sistemi keşfetmek için menüyü kullanabilirsiniz.
      </p>
      <br>
      <blockquote class="welcome-quote">
        “Başarı, küçük adımların toplamıdır.”
      </blockquote>
    </div>
  </VCard>
</template>

<style scoped>
.welcome-page {
  display: flex;
  align-items: center;
  justify-content: center;
  /* background: linear-gradient(120deg, #536271 0%, #e0e7ef 100%); */
  min-block-size: 70vh;
}

.welcome-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  border-radius: 18px;
  /* background: #fff; */
  box-shadow: 0 4px 24px 0 rgba(60, 72, 100, 8%);
  inline-size: 100%;
  max-inline-size: 720px;
  padding-block: 2.5rem 2rem;
  padding-inline: 2rem;
  text-align: center;
}

.welcome-image {
  inline-size: 120px;
  margin-block-end: 1.5rem;
  user-select: none;
}

.welcome-title {
  /* color: #2a3a4b; */
  font-size: 2rem;
  font-weight: 700;
  margin-block-end: 1rem;
}

.welcome-desc {
  /* color: #4b5563; */
  font-size: 1.08rem;
  margin-block-end: 1.5rem;
}

.welcome-quote {
  margin: 0;
  color: #2563eb;
  font-size: 1.6rem;
  font-style: italic;
}

@media (max-width: 600px) {
  .welcome-card {
    inline-size: 100%;
    max-inline-size: 98vw;
    padding-block: 1.2rem;
    padding-inline: 0.5rem;
  }

  .welcome-image {
    inline-size: 80px;
  }
}
</style>
