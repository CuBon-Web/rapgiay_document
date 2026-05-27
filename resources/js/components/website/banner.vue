<template>
  <div class="banner-manage">
    <h3 class="page-title">Quản lý banner</h3>
    <p class="banner-manage__hint">
      Tải ảnh riêng cho PC (ngang, rộng) và Mobile (dọc hoặc tỷ lệ phù hợp màn hình điện thoại).
    </p>

    <div class="banner-manage__tabs">
      <button
        type="button"
        class="banner-manage__tab"
        :class="{ 'is-active': activeTab === 'pc' }"
        @click="activeTab = 'pc'"
      >
        <vs-icon icon="desktop_windows" size="small"></vs-icon>
        Banner PC
        <span class="banner-manage__count">{{ objDataPc.length }}</span>
      </button>
      <button
        type="button"
        class="banner-manage__tab"
        :class="{ 'is-active': activeTab === 'mobile' }"
        @click="activeTab = 'mobile'"
      >
        <vs-icon icon="phone_iphone" size="small"></vs-icon>
        Banner Mobile
        <span class="banner-manage__count">{{ objDataMobile.length }}</span>
      </button>
    </div>

    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="banner-manage__panel-head">
              <h4 v-if="activeTab === 'pc'">Banner hiển thị trên máy tính / tablet ngang</h4>
              <h4 v-else>Banner hiển thị trên điện thoại</h4>
              <span class="banner-manage__size-tip" v-if="activeTab === 'pc'">Gợi ý: 1920×800 px</span>
              <span class="banner-manage__size-tip" v-else>Gợi ý: 750×900 px hoặc 1080×1350 px</span>
            </div>

            <div
              class="row banner-manage__item"
              v-for="(item, key) in currentList"
              :key="activeTab + '-' + key"
            >
              <div class="col-md-3">
                <div class="form-group">
                  <image-upload
                    type="avatar"
                    v-model="item.image"
                    :title="activeTab === 'pc' ? 'banner-pc' : 'banner-mobile'"
                  ></image-upload>
                </div>
              </div>
              <div class="col-md-9">
                <div class="form-group">
                  <label>Tiêu đề</label>
                  <label
                    class="banner-manage__remove"
                    title="Xóa banner"
                    v-if="key != 0"
                    @click="removeBanner(key)"
                  >
                    <vs-icon icon="clear"></vs-icon>
                  </label>
                  <vs-input
                    type="text"
                    v-model="item.title"
                    size="default"
                    placeholder="Tiêu đề banner"
                    class="w-100"
                  />
                </div>
                <div class="form-group">
                  <label>Mô tả</label>
                  <vs-input
                    type="text"
                    v-model="item.description"
                    size="default"
                    placeholder="Mô tả banner"
                    class="w-100"
                  />
                </div>
                <div class="form-group">
                  <label>Link</label>
                  <vs-input
                    type="text"
                    v-model="item.link"
                    size="default"
                    placeholder="Link khi bấm vào banner"
                    class="w-100"
                  />
                </div>
                <div class="form-group">
                  <label>Trạng thái</label>
                  <vs-select v-model="item.status">
                    <vs-select-item value="1" text="Hiện" />
                    <vs-select-item value="0" text="Ẩn" />
                  </vs-select>
                </div>
              </div>
              <hr class="banner-manage__divider" />
            </div>

            <div class="banner-manage__actions">
              <vs-button color="primary" @click="saveBanners">Lưu tất cả</vs-button>
              <vs-button color="success" type="border" @click="addBanner">Thêm banner {{ activeTab === 'pc' ? 'PC' : 'Mobile' }}</vs-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from "vuex";

const emptyBanner = () => ({
  image: "",
  status: 1,
  link: "",
  title: "",
  description: "",
  device: "pc",
});

export default {
  name: "banner",
  data() {
    return {
      activeTab: "pc",
      objDataPc: [emptyBanner()],
      objDataMobile: [{ ...emptyBanner(), device: "mobile" }],
    };
  },
  computed: {
    currentList() {
      return this.activeTab === "pc" ? this.objDataPc : this.objDataMobile;
    },
  },
  methods: {
    ...mapActions(["saveBanner", "loadings", "listBanner"]),
    saveBanners() {
      const payload = [
        ...this.objDataPc.map((item) => ({ ...item, device: "pc" })),
        ...this.objDataMobile.map((item) => ({ ...item, device: "mobile" })),
      ];
      this.loadings(true);
      this.saveBanner({ data: payload })
        .then(() => {
          this.loadings(false);
          this.$success("Lưu banner thành công");
        })
        .catch(() => {
          this.loadings(false);
          this.$error("Lưu banner thất bại");
        });
    },
    addBanner() {
      const item = emptyBanner();
      item.device = this.activeTab;
      if (this.activeTab === "pc") {
        this.objDataPc.push(item);
      } else {
        this.objDataMobile.push({ ...item, device: "mobile" });
      }
    },
    removeBanner(index) {
      if (this.activeTab === "pc") {
        this.objDataPc.splice(index, 1);
      } else {
        this.objDataMobile.splice(index, 1);
      }
    },
    listBanners() {
      this.loadings(true);
      this.listBanner()
        .then((response) => {
          this.loadings(false);
          const rows = response.data || [];
          const pc = rows.filter((row) => !row.device || row.device === "pc");
          const mobile = rows.filter((row) => row.device === "mobile");
          this.objDataPc = pc.length ? pc : [emptyBanner()];
          this.objDataMobile = mobile.length
            ? mobile
            : [{ ...emptyBanner(), device: "mobile" }];
        })
        .catch(() => {
          this.loadings(false);
        });
    },
  },
  mounted() {
    this.listBanners();
  },
};
</script>

<style scoped>
.banner-manage__hint {
  color: #6c757d;
  font-size: 14px;
  margin: -8px 0 16px;
}
.banner-manage__tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.banner-manage__tab {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  background: #fff;
  color: #495057;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}
.banner-manage__tab:hover {
  border-color: #7367f0;
  color: #7367f0;
}
.banner-manage__tab.is-active {
  background: #7367f0;
  border-color: #7367f0;
  color: #fff;
}
.banner-manage__count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  font-size: 12px;
  background: rgba(0, 0, 0, 0.08);
}
.banner-manage__tab.is-active .banner-manage__count {
  background: rgba(255, 255, 255, 0.25);
}
.banner-manage__panel-head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #eee;
}
.banner-manage__panel-head h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}
.banner-manage__size-tip {
  font-size: 13px;
  color: #7367f0;
}
.banner-manage__item {
  margin-bottom: 8px;
}
.banner-manage__remove {
  float: right;
  cursor: pointer;
}
.banner-manage__divider {
  border: 0;
  border-top: 1px solid rgba(4, 4, 4, 0.15);
  width: 100%;
  margin: 8px 0 20px;
}
.banner-manage__actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 8px;
}
</style>
