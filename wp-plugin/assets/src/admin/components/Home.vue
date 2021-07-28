<template>
    <div class="home">
        <span>{{ msg }}</span>
        <div v-if="!orders">Loading orders...</div>
        <div v-for="order in orders" :key="order.id">
          {{order.id}}

          <div v-for="item in order.line_items" :key="item.id">
            Item ID: {{item.id}}

          <div v-for="meta in item.meta_data" :key="meta.id"> 
            Meta: {{meta.id}} <br>
            FPD description: {{meta.value.product[0].title}}
          </div>
          </div>
        </div>
      <canvas id="c"></canvas>
    </div>
</template>

<script>
import axios from "axios";
/*
import OAuth from "./OAuth";
import crypto from "crypto";
*/
export default {
  name: "Home",

  data() {
    return {
      msg: "Plugin Admin Orders",
      c_key: "ck_17beae6881672c7d88dd434996eae7c2dd54fab1",
      c_secret: "cs_6502bd87eac95a65c90cdb4537efca073fc67e41",
      orders: null
    };
  },
  async created() {
    /*
    const oauth = OAuth({
      consumer: {
        key: this.c_key,
        secret: this.c_secret
      },
      signature_method: "HMAC-SHA1",
      hash_function(base_string, key) {
        return crypto
          .createHmac("sha1", key)
          .update(base_string)
          .digest("base64");
      }
    });
    const requestData = {
      url: `${window.location.origin}/wp-json/wc/v2/orders`,
      method: "GET",
      data: {}
    };

    this.orders = (await axios.get(requestData.url, {
      headers: oauth.toHeader(oauth.authorize(requestData))
    })).data;
    */

    this.orders = (await axios.get(
      `${window.location.origin}/wp-json/wc/v2/orders?status=processing`,
      {
        auth: {
          username: this.c_key,
          password: this.c_secret
        }
      }
    )).data;

    // Parse FPD Values
    this.orders = this.orders.map(order => {
      order.line_items.map(item => {
        item.meta_data.map(meta => {
          meta.value = this.getFPDParsedValues(meta.value);
        });
      });
      return order;
    });
    
  },
  methods: {
    getFPDParsedValues(value) {
      const parsed = JSON.parse(value.replace(/\\\"/g, '"'));
      return parsed;
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
