<script setup>
import {onMounted, ref} from 'vue';
import {useRouter} from 'vue-router';

const router = useRouter();

let invoices = ref([]);
let searchInvoice = ref([]);
let selectedInvoices = ref([]); // Add this for tracking selected invoices

onMounted(async () => {
  getInvoices();
});

const getInvoices = async () => {
  let response = await axios.get('/api/invoices');
  invoices.value = response.data.invoices;
}

const search = async () => {
  let response = await axios.get('/api/search-invoice?search=' + searchInvoice.value);
  invoices.value = response.data.invoices;
}

const newInvoice = async () => {
  let form = await axios.get('/api/invoices/create');
  router.push('/invoices/create');
}
const chatAi = async () => {
  router.push('/chat-ai');
}

const onShow = async (id) => {
  router.push({name: 'invoiceShow', params: {id: id}});
}

// Modified sendAi function to include selected invoice IDs
const sendAi = async () => {
  const formData = {
    invoiceIds: selectedInvoices.value
  };

  try {
    await axios.post('/api/send-ai', formData);
    selectedInvoices.value = []; // Clear selections after successful send
    router.push('/');
  } catch (error) {
    console.log(error);
  }
}

// Add function to toggle all checkboxes
const toggleAllInvoices = (event) => {
  if (event.target.checked) {
    selectedInvoices.value = invoices.value.map(invoice => invoice.id);
  } else {
    selectedInvoices.value = [];
  }
}

</script>

<template><div class="container">
  <!--==================== INVOICE LIST ====================-->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-title">Invoices</h2>
          <div>
            <button class="btn btn-secondary" @click="newInvoice">New Invoice</button>
            <button class="btn btn-success" @click="sendAi">Send AI ({{ selectedInvoices.length }} selected)</button>
            <button class="btn btn-primary" @click="chatAi">Chat AI</button>
          </div>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <select class="form-select">
                <option value="">Filter</option>
              </select>
            </div>
            <div class="col-md-6">
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" v-model="searchInvoice" @keyup="search()" placeholder="Search invoice">
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>
                    <input type="checkbox" @change="toggleAllInvoices" :checked="selectedInvoices.length === invoices.length && invoices.length > 0"> AI Status
                  </th>
                  <th>ID</th>
                  <th>Date</th>
                  <th>Number</th>
                  <th>Customer</th>
                  <th>Due Date</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in invoices" :key="item.id" v-if="invoices.length > 0">
                  <td>
                    <input type="checkbox" :value="item.id" v-model="selectedInvoices" :checked="item.ai_status === 1" :disabled="item.ai_status === 1">
                  </td>
                  <td><a href="#" @click="onShow(item.id)">#{{ item.id }}</a></td>
                  <td>{{ item.date }}</td>
                  <td>#{{ item.number }}</td>
                  <td v-if="item.customer">{{ item.customer.name }}</td>
                  <td v-else></td>
                  <td>{{ item.due_date }}</td>
                  <td>$ {{ item.total }}</td>
                </tr>
                <tr v-else>
                  <td colspan="8" class="text-center">No invoices found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</template>
