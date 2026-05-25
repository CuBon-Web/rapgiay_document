<template>
    <div>
      <div class="row">
        <div class="col-md-8 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <div class="form-group">
                <label>Tên tài liệu</label>
                <vs-input
                  type="text"
                  size="default"
                  placeholder="Tên tài liệu"
                  class="w-100"
                  v-model="objData.name"
                />
              </div>
              <div class="form-group drive-link-field">
                <label>Link Google Drive</label>
                <vs-input
                  type="url"
                  size="default"
                  class="w-100"
                  placeholder="https://drive.google.com/file/d/..."
                  v-model="objData.origin"
                />
                <p class="drive-link-note mb-0">
                  Lưu link file tài liệu (chia sẻ public). Hệ thống dùng trường này để giao file sau khi khách thanh toán.
                </p>
              </div>
              <div class="form-group">
                <label>Nội dung</label>
                <TinyMce
                  v-model="objData.content[0].content"
                  :focus-keyword="seoData.focusKeyword"
                />
                <el-button size="small" @click="showSettingLangExist('content')">Đa ngôn ngữ</el-button>
                 <div class="dropLanguage" v-if="showLang.content == true">
                    <div class="form-group" v-for="item,index in lang" :key="index">
                        <label v-if="index != 0">{{item.name}}</label>
                        <TinyMce v-if="index != 0" v-model="objData.content[index].content" />
                    </div>
                </div>
              </div>
              <div class="form-group">
                <label>Mô tả ngắn</label>
                <vs-textarea v-model="objData.description[0].content" />
                <el-button size="small" @click="showSettingLangExist('description')">Đa ngôn ngữ</el-button>
                 <div class="dropLanguage" v-if="showLang.description == true">
                    <div class="form-group" v-for="item,index in lang" :key="index">
                        <label v-if="index != 0">{{item.name}}</label>
                        <vs-textarea v-if="index != 0" v-model="objData.description[index].content" />
                    </div>
                </div>
              </div>
              <div class="form-group">
                <label>Ảnh bìa tài liệu <span class="text-muted">(không bắt buộc)</span></label>
                <ImageMulti v-model="objData.images" :title="'san-pham'"/> 
              </div>
              
              <div class="row">
              <div class="form-group col-6">
                <label>Giá bán</label>
                <vs-input
                  type="number"
                  size="default"
                  class="w-100"
                  v-model="objData.price"
                />
              </div>
              <div class="form-group col-6">
                <label>Giá khuyến mãi</label>
                <vs-input
                  type="number"
                  size="default"
                  class="w-100"
                  v-model="objData.discount"
                />
              </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <div class="form-group">
                <label>Trạng thái</label>
                <vs-select v-model="objData.status">
                  <vs-select-item value="1" text="Còn hàng" />
                  <vs-select-item value="0" text="Hết hàng" />
                </vs-select>
              </div>
              <div class="form-group">
                <label>Số lượng</label>
                <vs-input
                  type="text"
                  size="default"
                  placeholder="Số lượng"
                  class="w-100"
                  v-model="objData.qty"
                />
              </div>
              <div class="form-group">
                <div class="compact-meta-box">
                  <div class="compact-meta-title">Thiết lập hiển thị</div>
                  <div class="compact-meta-grid">
                    <div>
                      <label class="compact-label">Danh mục tài liệu</label>
                      <vs-select
                        class="selectExample w-100"
                        v-model="objData.category"
                        placeholder="Danh mục"
                        @change="findCategoryType()"
                      >
                      <vs-select-item
                          value="0"
                          text="Không danh mục"
                        />
                        <vs-select-item
                          :value="item.id"
                          :text="JSON.parse(item.name)[0].content"
                          v-for="(item, index) in cate"
                          :key="'f' + index"
                        />
                      </vs-select>
                    </div>
                    <div>
                      <label class="compact-label">Danh mục cấp 1</label>
                      <vs-select
                        class="selectExample w-100"
                        v-model="objData.type_cate"
                        placeholder="Loại"
                        :disabled=" type_cate.length == 0"
                      >
                        <vs-select-item
                          :value="item.id"
                          :text="JSON.parse(item.name)[0].content"
                          v-for="(item, index) in type_cate"
                          :key="'v' + index"
                        />
                      </vs-select>
                    </div>
                    <div>
                      <label class="compact-label">tài liệu nổi bật</label>
                      <el-radio-group v-model="objData.discountStatus" size="mini" class="compact-radio-group">
                        <el-radio-button :label="1">Có</el-radio-button>
                        <el-radio-button :label="0">Không</el-radio-button>
                      </el-radio-group>
                    </div>
                    <div>
                      <label class="compact-label">Hiển thị trang chủ</label>
                      <el-radio-group v-model="objData.home_status" size="mini" class="compact-radio-group">
                        <el-radio-button :label="1">Có</el-radio-button>
                        <el-radio-button :label="0">Không</el-radio-button>
                      </el-radio-group>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group tag-manager">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="mb-0">Thẻ tag tài liệu</label>
                  <span class="tag-selected-count">{{ objData.tags.length }} đã chọn</span>
                </div>
                <p class="tag-manager-note mb-2">
                  Chọn nhanh tag có sẵn hoặc tạo danh mục/tag mới ngay tại đây.
                </p>
                <vs-select
                  multiple
                  class="selectExample w-100 tag-multi-select"
                  v-model="objData.tags"
                  :placeholder="isTagCategoryRequired ? '-- Vui lòng chọn danh mục tài liệu trước --' : '-- Chọn thẻ tag --'"
                  :disabled="isTagCategoryRequired"
                >
                  <div :key="`tag-group-${item.slug}-${index}`" v-for="(item, index) in tags">
                    <vs-select-group :title="item.name" v-if="item.tags && item.tags.length > 0">
                      <vs-select-item
                        :key="`tag-item-${item.slug}-${tag.slug}-${tagIndex}`"
                        :value="buildTagValue(tag, item)"
                        :text="tag.name"
                        v-for="(tag, tagIndex) in item.tags"
                      />
                    </vs-select-group>
                  </div>
                </vs-select>
                <div v-if="isTagCategoryRequired" class="tag-category-warning mt-2">
                  <i class="el-icon-warning-outline"></i>
                  Bạn cần chọn Danh mục tài liệu trước để hệ thống tải danh sách tag tương ứng.
                </div>
                <div class="tag-quick-create mt-2">
                  <div class="tag-create-row">
                    <vs-input
                      class="w-100"
                      v-model="tagEditor.categoryName"
                      placeholder="Tên danh mục tag mới"
                      @keyup.enter.native="createTagCategoryInline"
                    />
                    <el-button
                      size="mini"
                      type="primary"
                      plain
                      :loading="tagEditor.creatingCategory"
                      @click="createTagCategoryInline"
                    >
                      Tạo danh mục
                    </el-button>
                  </div>
                  <div class="tag-create-row">
                    <vs-select
                      class="selectExample w-100"
                      v-model="tagEditor.selectedCategorySlug"
                      placeholder="Chọn danh mục để tạo tag"
                    >
                      <vs-select-item
                        :key="`tag-cate-${item.slug}-${index}`"
                        :value="item.slug"
                        :text="item.name"
                        v-for="item, index in tags"
                      />
                    </vs-select>
                    <vs-input
                      class="w-100"
                      v-model="tagEditor.tagName"
                      placeholder="Tên tag mới"
                      @keyup.enter.native="createTagInline"
                    />
                    <el-button
                      size="mini"
                      type="success"
                      plain
                      :loading="tagEditor.creatingTag"
                      @click="createTagInline"
                    >
                      Tạo tag
                    </el-button>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Slug tài liệu</label>
                <vs-input
                  class="w-100"
                  v-model="seoData.slug"
                  placeholder="duong-dan-san-pham"
                />
              </div>
              <seo-assistant
                :model="seoData"
                :article-title="objData.name"
                :article-content="objData.content[0].content"
                :article-description="objData.description[0].content"
                preview-path="san-pham"
                @update:model="seoData = $event"
                @auto-optimize="autoOptimizeSeo"
                @insert-keyword="insertKeywordToContent"
              />
            </div>
          </div> 
        </div>
      </div>
      <div class="row fixxed">
        <div class="col-12">
          <div class="saveButton">
            <vs-button color="primary" @click="saveProducts"
              >Thêm tài liệu</vs-button
            >
          </div>
        </div>
      </div>
    </div>
</template>


<script>
import { mapActions } from "vuex";
import TinyMce from "../_common/tinymce";
import ImageMulti from "../_common/upload_image_multi";
import SeoAssistant from "../_common/seo_assistant";
import "tinymce/icons/default/icons.min.js";
export default {
  name: "product",
  data() {
    return {
      cate: [],
      type_cate: [],
      tags: [],
      useGlobalTags: true,
      tagEditor: {
        categoryName: "",
        tagName: "",
        selectedCategorySlug: "",
        creatingCategory: false,
        creatingTag: false,
      },
      showLang: {
        title: false,
        content: false,
        description: false,
      },
      lang: [],
      errors: [],
      objData: {
        lang: "",
        variant: [],
        name: "",
        size: [],
        tags: [],
        price: 0,
        discount: 0,
        preserve: [],
        ingredient: "",
        images: [],
        description: [
          {
            lang_code: "vi",
            content: "",
          },
        ],
        content: [
          {
            lang_code: "vi",
            content: "",
          },
        ],
        category: 0,
        status: 1,
        qty: 100,
        discountStatus: 0,
        type_cate: 0,
        type_two: 0,
        species: [{ detail: "" }],
        origin: "",
        thickness: "",
        hang_muc: "",
        service_id: 0,
        lungtung: [],
        status_variant: 0,
        home_status: 0,
      },
      seoData: {
        focusKeyword: "",
        seoTitle: "",
        metaDescription: "",
        slug: ""
      },
      syncPaused: false
    };
  },
  components: {
    TinyMce,
    ImageMulti,
    SeoAssistant,
  },
  computed: {
    isTagCategoryRequired() {
      return !this.useGlobalTags && Number(this.objData.category) === 0;
    },
  },
  watch: {
    "objData.name"(value) {
      if (this.syncPaused) return;
      this.withSyncPause(() => {
        this.seoData.seoTitle = value || "";
        this.seoData.slug = this.slugifySeo(value || "");
      });
    },
    "objData.description.0.content"(value) {
      if (this.syncPaused) return;
      this.withSyncPause(() => {
        this.seoData.metaDescription = (value || "").slice(0, 160);
      });
    },
    "seoData.seoTitle"(value) {
      if (this.syncPaused) return;
      this.withSyncPause(() => {
        this.objData.name = value || "";
        this.seoData.slug = this.slugifySeo(value || "");
      });
    },
    "seoData.metaDescription"(value) {
      if (this.syncPaused) return;
      this.withSyncPause(() => {
        this.objData.description[0].content = value || "";
      });
    },
    "seoData.slug"(value) {
      if (this.syncPaused) return;
      const normalized = this.slugifySeo(value || "");
      if (normalized === value) return;
      this.withSyncPause(() => {
        this.seoData.slug = normalized;
      });
    }
  },
  methods: {
    ...mapActions([
      "editId",
      "saveProduct",
      "listCate",
      "loadings",
      "listLanguage",
      "findTypeCate",
      "findTags",
      "getSetting",
      "saveTagCate",
      "saveTag",
    ]),
    buildTagValue(tag, category) {
      if (!tag || !category) return "";
      return `${tag.slug}-${category.slug}`;
    },
    syncTagSelections() {
      const validTagValues = new Set();
      this.tags.forEach((category) => {
        (category.tags || []).forEach((tag) => {
          validTagValues.add(this.buildTagValue(tag, category));
        });
      });
      this.objData.tags = (this.objData.tags || []).filter((value) => validTagValues.has(value));
    },
    refreshTagsByCategory() {
      const requestCategory = this.useGlobalTags ? 0 : Number(this.objData.category || 0);
      if (!this.useGlobalTags && requestCategory === 0) {
        this.tags = [];
        this.objData.tags = [];
        this.tagEditor.selectedCategorySlug = "";
        return Promise.resolve();
      }
      return this.findTags(requestCategory)
        .then((response) => {
          this.tags = Array.isArray(response.data) ? response.data : [];
          if (!this.tagEditor.selectedCategorySlug && this.tags.length > 0) {
            this.tagEditor.selectedCategorySlug = this.tags[0].slug || "";
          }
          if (
            this.tagEditor.selectedCategorySlug &&
            !this.tags.some((item) => item.slug === this.tagEditor.selectedCategorySlug)
          ) {
            this.tagEditor.selectedCategorySlug = this.tags.length > 0 ? (this.tags[0].slug || "") : "";
          }
          this.syncTagSelections();
        })
        .catch(() => {
          this.tags = [];
          this.objData.tags = [];
          this.tagEditor.selectedCategorySlug = "";
        });
    },
    createTagCategoryInline() {
      const categoryName = (this.tagEditor.categoryName || "").trim();
      if (!categoryName) {
        this.$error("Nhập tên danh mục tag mới");
        return;
      }
      if (!this.useGlobalTags && (!this.objData.category || Number(this.objData.category) === 0)) {
        this.$error("Hãy chọn danh mục tài liệu trước khi tạo danh mục tag");
        return;
      }
      this.tagEditor.creatingCategory = true;
      this.saveTagCate({
        name: categoryName,
        status: 1,
        status_filter: 1,
        cate_product_id: this.useGlobalTags ? 0 : this.objData.category,
      })
        .then(() => {
          this.$success("Đã tạo danh mục tag");
          this.tagEditor.categoryName = "";
          return this.refreshTagsByCategory();
        })
        .then(() => {
          const createdCategory = this.tags.find(
            (item) => (item.name || "").trim().toLowerCase() === categoryName.toLowerCase()
          );
          if (createdCategory) {
            this.tagEditor.selectedCategorySlug = createdCategory.slug || "";
          }
        })
        .catch(() => {
          this.$error("Không thể tạo danh mục tag");
        })
        .finally(() => {
          this.tagEditor.creatingCategory = false;
        });
    },
    createTagInline() {
      const tagName = (this.tagEditor.tagName || "").trim();
      if (!tagName) {
        this.$error("Nhập tên tag mới");
        return;
      }
      const selectedCategory = this.tags.find(
        (item) => item.slug === this.tagEditor.selectedCategorySlug
      );
      if (!selectedCategory || !selectedCategory.id) {
        this.$error("Chọn danh mục tag trước khi tạo tag");
        return;
      }
      this.tagEditor.creatingTag = true;
      this.saveTag({
        name: tagName,
        status: 1,
        cate_tag_id: selectedCategory.id,
        cate_product_id: this.useGlobalTags ? 0 : this.objData.category,
      })
        .then(() => {
          this.$success("Đã tạo tag mới");
          this.tagEditor.tagName = "";
          return this.refreshTagsByCategory();
        })
        .then(() => {
          const updatedCategory = this.tags.find((item) => item.slug === selectedCategory.slug);
          const createdTag = (updatedCategory && updatedCategory.tags || []).find(
            (item) => (item.name || "").trim().toLowerCase() === tagName.toLowerCase()
          );
          if (createdTag) {
            const tagValue = this.buildTagValue(createdTag, updatedCategory);
            if (!this.objData.tags.includes(tagValue)) {
              this.objData.tags.push(tagValue);
            }
          }
        })
        .catch(() => {
          this.$error("Không thể tạo tag");
        })
        .finally(() => {
          this.tagEditor.creatingTag = false;
        });
    },
    isValidDriveLink(url) {
      const value = (url || "").trim();
      if (!value) return false;
      return /https?:\/\/(drive\.google\.com|docs\.google\.com)\//i.test(value);
    },
    slugifySeo(value) {
      return (value || "")
        .toString()
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/g, "d")
        .replace(/[^a-z0-9\s-]/g, "")
        .trim()
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-");
    },
    onSlugInput(value) {
      this.seoData.slug = this.slugifySeo(value);
    },
    autoOptimizeSeo() {
      const title = (this.objData.name || "").trim();
      const shortDesc = (this.objData.description[0].content || "").trim();
      this.withSyncPause(() => {
        if (!this.seoData.focusKeyword) {
          this.seoData.focusKeyword = this.slugifySeo(title).replace(/-/g, " ").split(" ").slice(0, 4).join(" ");
        }
        this.seoData.seoTitle = title ? `${title} | CuBon` : this.seoData.seoTitle;
        this.seoData.metaDescription = (shortDesc || "").slice(0, 160);
        this.seoData.slug = this.slugifySeo(title);
      });
    },
    insertKeywordToContent({ target, keyword }) {
      if (!keyword) return;
      this.withSyncPause(() => {
        if (target === "title") {
          if (!this.objData.name.toLowerCase().includes(keyword.toLowerCase())) {
            this.objData.name = `${this.objData.name} ${keyword}`.trim();
          }
        } else if (target === "description") {
          if (!this.objData.description[0].content.toLowerCase().includes(keyword.toLowerCase())) {
            this.objData.description[0].content = `${this.objData.description[0].content} ${keyword}`.trim();
          }
        }
      });
    },
    withSyncPause(callback) {
      this.syncPaused = true;
      callback();
      this.$nextTick(() => {
        this.syncPaused = false;
      });
    },
    saveProducts() {
      this.errors = [];
      if (this.objData.name == "") this.errors.push("Tên không được để trống");
      if (this.objData.content[0].content == "") this.errors.push("Nội dung không được để trống");
      if (this.objData.description[0].content == "") this.errors.push("Mô tả không được để trống");
      if (!this.isValidDriveLink(this.objData.origin)) {
        this.errors.push("Link Google Drive không hợp lệ (cần link drive.google.com hoặc docs.google.com)");
      }
      if (this.objData.category == 0) this.errors.push("Chọn danh mục tài liệu");
      if (this.errors.length > 0) {
        this.errors.forEach((value) => {
          this.$error(value);
        });
        return;
      }

      this.loadings(true);
      const payload = {
        ...this.objData,
        origin: (this.objData.origin || "").trim(),
        size: [],
        variant: [],
        lungtung: [],
        status_variant: 0,
        seo_title: this.seoData.seoTitle,
        meta_description: this.seoData.metaDescription,
        focus_keyword: this.seoData.focusKeyword,
        slug: this.seoData.slug || this.slugifySeo(this.objData.name),
      };
      this.saveProduct(payload)
        .then(() => {
          this.loadings(false);
          this.$router.push({ name: "listProduct" });
          this.$success("Thêm tài liệu thành công");
        })
        .catch(() => {
          this.loadings(false);
        });
    },
    
    findCategoryType() {
      this.findTypeCate(this.objData.category).then((response) => {
        this.type_cate = response.data;
      });
      this.refreshTagsByCategory();
    },
    loadTagModeSetting() {
      return this.getSetting()
        .then((response) => {
          const settingData = response && response.data ? response.data : {};
          this.useGlobalTags = settingData.use_global_tags !== undefined
            ? Number(settingData.use_global_tags) === 1
            : true;
        })
        .catch(() => {
          this.useGlobalTags = true;
        })
        .then(() => this.refreshTagsByCategory());
    },
    showSettingLangExist(value, name = "content") {
      if (value == "content") {
        this.showLang.content = !this.showLang.content;
        this.lang.forEach((value, index) => {
          if (
            !this.objData.content[index] &&
            value.code != this.objData.content[0].lang_code
          ) {
            var oj = {};
            oj.lang_code = value.code;
            oj.content = "";
            this.objData.content.push(oj);
          }
        });
      }
      if (value == "description") {
        this.showLang.description = !this.showLang.description;
        this.lang.forEach((value, index) => {
          if (
            !this.objData.description[index] &&
            value.code != this.objData.description[0].lang_code
          ) {
            var oj = {};
            oj.lang_code = value.code;
            oj.content = "";
            this.objData.description.push(oj);
          }
        });
      }
    },
    listLang() {
      this.listLanguage()
        .then((response) => {
          this.loadings(false);
          this.lang = response.data;
        })
        .catch((error) => {});
    },
  },
  mounted() {
    this.loadings(true);
    this.listCate().then((response) => {
      this.loadings(false);
      this.cate = response.data;
    });
    this.listLang();
    this.loadTagModeSetting();
  },
};
</script>
<style scoped>
.centerx li {
    list-style: none!important;
}
.centerx, .con-notifications, .con-notifications-position {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
}
.tag-manager {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px;
  background: #fcfcfd;
}
.tag-manager-note {
  color: #6b7280;
  font-size: 12px;
}
.tag-multi-select {
  border-radius: 10px;
}
.tag-multi-select::v-deep .vs-con-select {
  min-height: 42px;
  border: 1px solid #dbe2ea;
  border-radius: 10px;
  background: #fff;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.tag-multi-select::v-deep .vs-con-select:hover {
  border-color: #c3ceda;
}
.tag-multi-select::v-deep .vs-con-select.active {
  border-color: #409eff;
  box-shadow: 0 0 0 3px rgba(64, 158, 255, 0.15);
}
.tag-multi-select::v-deep .vs-con-select .vs-selected {
  margin: 4px 4px 0 0;
  border-radius: 999px;
  background: #eef6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  font-size: 12px;
  padding: 2px 8px;
}
.tag-multi-select::v-deep .vs-con-select .vs-selected .con-icon {
  font-size: 11px;
}
.tag-multi-select::v-deep .vs-con-select input {
  min-height: 32px;
  padding-left: 10px;
}
.tag-multi-select::v-deep .vs-con-select.isDisabled,
.tag-multi-select::v-deep .vs-con-select.is-disabled {
  background: #f3f4f6;
  border-color: #e5e7eb;
  opacity: 0.9;
}
.tag-selected-count {
  font-size: 12px;
  color: #4b5563;
  background: #eff6ff;
  border-radius: 999px;
  padding: 2px 10px;
}
.tag-category-warning {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #92400e;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 8px;
  padding: 8px 10px;
}
.tag-quick-create {
  border-top: 1px dashed #d1d5db;
  padding-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.tag-create-row {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
  align-items: center;
}
.tag-create-row:nth-child(2) {
  grid-template-columns: 1fr 1fr auto;
}
.drive-link-field {
  border: 1px solid #dbeafe;
  border-radius: 8px;
  padding: 12px;
  background: #f8fbff;
}
.drive-link-note {
  margin-top: 8px;
  color: #6b7280;
  font-size: 12px;
}
.compact-meta-box {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 10px;
  background: #fafafa;
}
.compact-meta-title {
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 8px;
}
.compact-meta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px 12px;
}
.compact-label {
  display: block;
  margin-bottom: 4px;
  font-size: 12px;
  color: #4b5563;
}
.compact-radio-group {
  display: inline-flex;
}
</style>