<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { CardDetails } from '@/types/payment'

// Original Pen: https://codepen.io/JavaScriptJunkie/pen/YzzNGeR

const cardData = ref<CardDetails>({
  number: '',
  name: '',
  expiry: '',
  cvv: ''
})

const isCardFlipped = ref(false)

const emit = defineEmits(['update:modelValue'])

watch(cardData, (newValue) => {
  emit('update:modelValue', newValue)
}, { deep: true })

const formattedCardNumberForDisplay = computed(() => {
  const value = cardData.value.number.replace(/\D/g, '')
  let result = ''
  for (let i = 0; i < 16; i++) {
    result += value[i] || '#'
    if ((i + 1) % 4 === 0 && i < 15) {
      result += ' '
    }
  }

  return result
})

const formattedExpiryForDisplay = computed(() => {
  const value = cardData.value.expiry.replace(/\D/g, '')
  const month = value.slice(0, 2).padEnd(2, 'M')
  const year = value.slice(2, 4).padEnd(2, 'Y')

  return `${month}/${year}`
})

function handleCardNumberInput(event: Event) {
  const input = event.target as HTMLInputElement
  const value = input.value.replace(/\D/g, '').slice(0, 16)

  cardData.value.number = value
  input.value = value.replace(/(\d{4})/g, '$1 ').trim()
}

function handleExpiryInput(event: Event) {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '').slice(0, 4)

  if (value.length > 2) {
    value = `${value.slice(0, 2)}/${value.slice(2)}`
  }

  cardData.value.expiry = value.replace('/', '')
  input.value = value
}

function focusCVV() {
  isCardFlipped.value = true
}

function blurCVV() {
  isCardFlipped.value = false
}
</script>

<template>
  <div class="card-form">
    <div class="card-list">
      <div
        class="card-item"
        :class="{ '-active': isCardFlipped }"
      >
        <div class="card-item__side -front">
          <div class="card-item__cover">
            <img
              src="/img/card/card-bg.webp"
              class="card-item__bg"
            >
          </div>
          <div class="card-item__wrapper">
            <div class="card-item__top">
              <img
                src="/img/card/chip.webp"
                class="card-item__chip"
              >
              <div class="card-item__type">
                <img
                  src="/img/card/visa.webp"
                  alt="visa"
                  class="card-item__typeImg"
                >
              </div>
            </div>
            <label class="card-item__number">
              <span
                v-for="(char, index) in formattedCardNumberForDisplay"
                :key="index"
              >{{ char }}</span>
            </label>
            <div class="card-item__content">
              <label class="card-item__info">
                <div class="card-item__holder">Card Holder</div>
                <div class="card-item__name">{{ cardData.name || 'Full Name' }}</div>
              </label>
              <div class="card-item__date">
                <label class="card-item__date-title">Expires</label>
                <label class="card-item__date-item">{{ formattedExpiryForDisplay }}</label>
              </div>
            </div>
          </div>
        </div>
        <div class="card-item__side -back">
          <div class="card-item__cover">
            <img
              src="/img/card/card-bg.webp"
              class="card-item__bg"
            >
          </div>
          <div class="card-item__band" />
          <div class="card-item__cvv">
            <div class="card-item__cvv-title">
              CVV
            </div>
            <div class="card-item__cvv-band">
              {{ cardData.cvv }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-form__inner">
      <div class="card-input">
        <label
          for="cardNumber"
          class="card-input__label"
        >Card Number</label>
        <input
          id="cardNumber"
          type="text"
          class="card-input__input"
          maxlength="19"
          @input="handleCardNumberInput"
        >
      </div>
      <div class="card-input">
        <label
          for="cardName"
          class="card-input__label"
        >Card Holder</label>
        <input
          id="cardName"
          v-model="cardData.name"
          type="text"
          class="card-input__input"
        >
      </div>
      <div class="card-form__row">
        <div class="card-form__col">
          <div class="card-input">
            <label
              for="cardMonth"
              class="card-input__label"
            >Expiration Date</label>
            <input
              id="cardMonth"
              type="text"
              class="card-input__input"
              placeholder="MM/YY"
              maxlength="5"
              @input="handleExpiryInput"
            >
          </div>
        </div>
        <div class="card-form__col -cvv">
          <div class="card-input">
            <label
              for="cardCvv"
              class="card-input__label"
            >CVV</label>
            <input
              id="cardCvv"
              v-model="cardData.cvv"
              type="text"
              class="card-input__input"
              maxlength="4"
              @focus="focusCVV"
              @blur="blurCVV"
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card-form {
  max-width: 570px;
  margin: auto;
  width: 100%;
}
.card-list {
  margin-bottom: -130px;
}
.card-item {
  max-width: 430px;
  height: 270px;
  margin-left: auto;
  margin-right: auto;
  position: relative;
  z-index: 2;
  width: 100%;
  transform-style: preserve-3d;
  transition: transform 0.6s;
}
.card-item.-active {
  transform: rotateY(180deg);
}
.card-item__side {
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 20px 60px 0 rgba(14, 42, 90, 0.55);
  transform-style: preserve-3d;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
}
.card-item__side.-back {
  transform: rotateY(180deg);
}
.card-item__cover {
  height: 100%;
  background-color: #1c1d27;
  position: absolute;
  width: 100%;
  left: 0;
  top: 0;
}
.card-item__bg {
  max-width: 100%;
  display: block;
  max-height: 100%;
  height: 100%;
  width: 100%;
  object-fit: cover;
}
.card-item__wrapper {
  font-family: "Source Code Pro", monospace;
  padding: 25px 15px;
  position: relative;
  z-index: 4;
  height: 100%;
  text-shadow: 7px 6px 10px rgba(14, 42, 90, 0.8);
  user-select: none;
}
.card-item__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 40px;
}
.card-item__chip {
  width: 60px;
}
.card-item__type {
  height: 45px;
  position: relative;
  display: flex;
  justify-content: flex-end;
  max-width: 100px;
  margin-left: auto;
  width: 100%;
}
.card-item__typeImg {
  max-width: 100%;
  object-fit: contain;
  max-height: 100%;
}
.card-item__number {
  font-weight: 500;
  line-height: 1;
  color: white;
  font-size: 27px;
  margin-bottom: 35px;
  display: inline-block;
  padding: 10px 15px;
  cursor: pointer;
}
.card-item__content {
  color: white;
  display: flex;
  align-items: flex-start;
}
.card-item__info {
  width: 100%;
  max-width: calc(100% - 85px);
  padding: 10px 15px;
  font-weight: 500;
  display: block;
  cursor: pointer;
}
.card-item__holder {
  opacity: 0.7;
  font-size: 13px;
  margin-bottom: 6px;
}
.card-item__name {
  font-size: 18px;
  line-height: 1;
  white-space: nowrap;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
}
.card-item__date {
  flex-wrap: wrap;
  font-size: 18px;
  margin-left: auto;
  padding: 10px 15px;
  line-height: 1;
  white-space: nowrap;
  max-width: 80px;
  cursor: pointer;
}
.card-item__date-title {
  opacity: 0.7;
  font-size: 13px;
  padding-bottom: 6px;
  width: 100%;
}
.card-item__band {
  background: rgba(0, 0, 19, 0.8);
  width: 100%;
  height: 50px;
  margin-top: 30px;
  position: relative;
  z-index: 2;
}
.card-item__cvv {
  text-align: right;
  position: relative;
  z-index: 2;
  padding: 15px;
}
.card-item__cvv-title {
  padding-right: 10px;
  font-size: 15px;
  font-weight: 500;
  color: #fff;
  margin-bottom: 5px;
}
.card-item__cvv-band {
  height: 45px;
  background: #fff;
  margin-bottom: 30px;
  text-align: right;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 10px;
  color: #1a3b5d;
  font-size: 18px;
  border-radius: 4px;
  box-shadow: 0 10px 20px -7px rgba(32, 56, 117, 0.35);
}
.card-form__inner {
  box-shadow: 0 30px 60px 0 rgba(90, 116, 148, 0.4);
  border-radius: 10px;
  padding: 180px 35px 35px 35px;
  background: #fff;
}
.card-input {
  margin-bottom: 20px;
}
.card-input__label {
  font-size: 14px;
  margin-bottom: 5px;
  font-weight: 500;
  color: #1a3b5d;
  width: 100%;
  display: block;
  user-select: none;
}
.card-input__input {
  width: 100%;
  height: 50px;
  border-radius: 5px;
  box-shadow: none;
  border: 1px solid #ced6e0;
  transition: all 0.3s ease-in-out;
  font-size: 18px;
  padding: 5px 15px;
  background: none;
  color: #1a3b5d;
  font-family: "Source Sans Pro", sans-serif;
}
.card-input__input:focus {
  border-color: #3d9cff;
  outline: none;
}
.card-form__row {
  display: flex;
  align-items: flex-start;
}
.card-form__col {
  flex: auto;
  margin-right: 35px;
}
.card-form__col:last-child {
  margin-right: 0;
}
.card-form__col.-cvv {
  max-width: 150px;
}
</style>
