<template>
  <div>
      <h3 class="page-title">Cài đặt chung</h3>
      <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body" >
                <div class="form-group">
                  <label>Tên doanh nghiệp</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.company"
                  />
                </div>
                <div class="form-group">
                  <label>Tên website</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.webname"
                  />
                </div>
                <div class="form-group">
                  <label>Văn phòng đại diện</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.address1"
                  />
                </div>
                <div class="form-group">
                  <label>Địa chỉ</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.address2"
                  />
                </div>
                <div class="form-group">
                  <label>Số điện thoại 1</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.phone1"
                  />
                </div>
                <div class="form-group">
                  <label>Số điện thoại 2</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.phone2"
                  />
                </div>
                <div class="form-group">
                  <label>Fax</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.fax"
                  />
                </div>
                <div class="form-group">
                  <label>Email</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.email"
                  />
                </div>
                <div class="form-group">
                  <label>Facebook</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.facebook"
                  />
                </div>
                <div class="form-group">
                  <label>Youtube</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.google"
                  />
                </div>
                <div class="form-group">
                  <label>Instagram</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.fbPixel"
                  />
                </div>
                <div class="form-group">
                  <label>Code Google analytics</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.GA"
                  />
                </div>
                <div class="form-group">
                  <label>Iframe GG map</label>
                  <vs-input
                    type="text"
                    size="default"
                    class="w-100"
                    v-model="objData.iframe_map"
                  />
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <h5 style="margin: 12px 0 8px;">Thông tin QR ngân hàng (ủng hộ)</h5>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Mã ngân hàng (BIN)</label>
                      <vs-input
                        type="text"
                        size="default"
                        class="w-100"
                        v-model="objData.bank_bin"
                        placeholder="VD: 970422"
                      />
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Số tài khoản</label>
                      <vs-input
                        type="text"
                        size="default"
                        class="w-100"
                        v-model="objData.bank_number"
                        placeholder="VD: 0123456789"
                      />
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Chủ tài khoản</label>
                      <vs-input
                        type="text"
                        size="default"
                        class="w-100"
                        v-model="objData.bank_owner"
                        placeholder="VD: NGUYEN VAN A"
                      />
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Nội dung chuyển khoản</label>
                      <vs-input
                        type="text"
                        size="default"
                        class="w-100"
                        v-model="objData.support_content"
                        placeholder="Ung ho de phat trien them nhieu tai lieu hay"
                      />
                    </div>
                  </div>
                  <div class="col-md-12" v-if="supportQrUrl">
                    <div class="form-group">
                      <label>Preview QR</label>
                      <div>
                        <img :src="supportQrUrl" alt="QR ngân hàng" style="max-width: 220px; border: 1px solid #eee; border-radius: 8px; padding: 6px;">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Chế độ tag sản phẩm</label>
                  <vs-select v-model="objData.use_global_tags">
                    <vs-select-item :value="1" text="Global (dùng chung toàn bộ)" />
                    <vs-select-item :value="0" text="Theo danh mục sản phẩm" />
                  </vs-select>
                </div>
                <div class="form-group">
                  <label>Favicon</label>
                  <image-upload type="avatar" v-model="objData.favicon" :title="'favicon'"></image-upload>
                </div>
                <div class="form-group">
                  <label>Logo</label>
                  <image-upload type="avatar" v-model="objData.logo" :title="'logo'"></image-upload>
                </div>
              <vs-button color="primary" @click="saveSetting">Lưu</vs-button>
            </div>
          </div>
        </div>
      </div>
    <!-- content-wrapper ends -->
  </div>
</template>


<script>
import { mapActions } from "vuex";
import { required } from "vuelidate/lib/validators";
import TinyMce from "../_common/tinymce";
export default {
  name: "setting",
  data() {
    return {
      objData:
        {
            webname : "",
            company : "",
            address1: "",
            address2 : "",
            phone1 : "",
            phone2 : "",
            fax : "",
            email : "",
            facebook : "",
            google : "",
            GA : "",
            fbPixel : "",
            iframe_map : "",
            favicon : "",
            logo : "",
            logo_mobi : "",
            logo_footer: "",
            footer_content:"",
            popupimage:"",
            statusPopup:0,
            linkpopup:"",
            statusImgHeader:0,
            imgHeader:"",
            linkImgHeader:"",
            use_global_tags: 1,
            facebook : "",
            google : "",
            GA : "",
            fbPixel : "",
            iframe_map : "",
            favicon : "",
            logo : "",
            bank_bin : "",
            bank_number : "",
            bank_owner : "",
            support_content : "Ung ho de phat trien them nhieu tai lieu hay"
        }
    };
  },
  components: {
    TinyMce
  },
  computed: {},
  watch: {},
  methods: {
    ...mapActions(["postSetting", "loadings","getSetting"]),
    saveSetting(){
      this.loadings(true);
      this.postSetting(this.objData).then(response => {
        this.loadings(false);
        this.$success('Cài đặt thành công');
      }).catch(error => {
        this.loadings(false);
        this.$error('Cài đặt thất bại');
      })
    },
    listSettings(){
      this.loadings(true);
      this.getSetting().then(response => {
        this.loadings(false);
        this.objData = {
          ...this.objData,
          ...(response.data || {}),
          use_global_tags: response.data && response.data.use_global_tags !== undefined
            ? Number(response.data.use_global_tags)
            : 1
        };
      }).catch(error => {
        this.loadings(false);;
      })
    }
  },
  computed: {
    supportQrUrl() {
      if (!this.objData.bank_bin || !this.objData.bank_number) return "";
      const content = this.objData.support_content || "Ung ho de phat trien them nhieu tai lieu hay";
      const owner = this.objData.bank_owner || this.objData.company || "";
      return `https://img.vietqr.io/image/${this.objData.bank_bin}-${this.objData.bank_number}-compact2.png?addInfo=${encodeURIComponent(content)}&accountName=${encodeURIComponent(owner)}`;
    },
  },
  mounted() {
    this.listSettings();
  }
};
</script>