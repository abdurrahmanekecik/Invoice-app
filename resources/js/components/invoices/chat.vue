<template>
  <div id="app" class="container py-5">
    <h1 class="text-center mb-4">AI Chat Interface</h1>
    <div class="card shadow">
      <div class="card-body">
        <div v-for="(message, index) in messages" :key="index" class="mb-3">
          <div class="fw-bold" v-if="message.sender === 'user'">You:</div>
          <div class="fw-bold text-primary" v-else>ChatGPT:</div>
          <div>{{ message.text }}</div>
        </div>
        <div v-if="loading" class="text-center my-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div class="input-group mt-3">
          <input
              type="text"
              class="form-control"
              v-model="userInput"
              placeholder="Type your message..."
              @keyup.enter="sendMessage"
              :disabled="loading"
          >
          <button class="btn btn-primary" @click="sendMessage" :disabled="loading">Send</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script >
export default {
  data() {
    return {
      userInput: '', // Kullanıcı girişini saklamak için
      messages: [],  // Mesaj listesini saklamak için
      loading: false // Yükleme durumunu saklamak için
    };
  },

  methods: {
    async sendMessage() {
      if (!this.userInput.trim()) {
        console.warn('Cannot send an empty message.');
        return;
      }

      this.messages.push({ sender: 'user', text: this.userInput });
      const userMessage = this.userInput;
      this.userInput = '';
      this.loading = true;

      try {
        const response = await axios.post('api/chat-ai', { message: userMessage });
        const chatResponse = response.data?.response || 'No response received.';
        this.messages.push({ sender: 'chatgpt', text: chatResponse });
      } catch (error) {
        console.error('Error:', error);
        this.messages.push({
          sender: 'chatgpt',
          text: `An error occurred: ${error.response?.data?.message || error.message}`
        });
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
