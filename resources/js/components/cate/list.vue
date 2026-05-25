<template>
  <!-- partial -->
  <div>
      <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title" @click="listCategory">Danh sách danh mục</h4>
              <p class="card-description">
                Thêm mới hoặc sửa chữa danh mục sản phẩm.
                <span v-if="canReorder" class="cate-reorder-hint">Kéo biểu tượng ⋮⋮ để thay đổi thứ tự hiển thị.</span>
                <span v-else-if="hasKeyword" class="cate-reorder-hint cate-reorder-hint--muted">Tắt tìm kiếm để sắp xếp thứ tự.</span>
              </p>
              <vs-button
                type="gradient"
                style="float:right;"
                :disabled="!$hasPermission('product.create')"
                @click="$goIfAllowed('product.create', { name: 'add_category' }, 'Bạn không có quyền thêm mới danh mục sản phẩm.')"
              >Thêm mới</vs-button>
              <vs-input
                icon="search"
                placeholder="Search"
                v-model="keyword"
                @keyup="searchCategory()"
              />

              <!-- Bảng kéo thả: dùng table HTML chuẩn (vs-table không có tbody) -->
              <div v-if="canReorder" ref="sortableTableWrap" class="cate-sortable-table">
                <table class="table table-hover cate-sortable-table__inner">
                  <thead>
                    <tr>
                      <th class="cate-th-sort">#</th>
                      <th>ID</th>
                      <th>Tên</th>
                      <th>Avatar</th>
                      <th>Title</th>
                      <th>Hành động</th>
                    </tr>
                  </thead>
                  <tbody ref="sortableTbody">
                    <tr
                      v-for="(tr, indextr) in list"
                      :key="tr.id"
                      :data-id="tr.id"
                      class="cate-sortable-row"
                    >
                      <td>
                        <span class="drag-handle" title="Kéo để sắp xếp">
                          <i class="material-icons notranslate">drag_indicator</i>
                        </span>
                        <span class="cate-sort-index">{{ indextr + 1 }}</span>
                      </td>
                      <td>{{ tr.id }}</td>
                      <td>{{ parseCateName(tr.name) }}</td>
                      <td>
                        <vs-avatar size="70px" :src="tr.avatar" />
                      </td>
                      <td>{{ tr.path }}</td>
                      <td>
                        <router-link :to="{name:'edit_category',params:{id:tr.id}}">
                          <vs-button vs-type="gradient" size="lagre" color="success" icon="edit"></vs-button>
                        </router-link>
                        <vs-button vs-type="gradient" size="lagre" color="red" icon="delete_forever" @click="confirmDestroy(tr.id)"></vs-button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Chế độ tìm kiếm: giữ vs-table -->
              <vs-table v-else max-items="5" pagination :data="list">
                <template slot="thead">
                  <vs-th>ID</vs-th>
                  <vs-th>Tên</vs-th>
                  <vs-th>Avatar</vs-th>
                  <vs-th>Title</vs-th>
                  <vs-th>Hành động</vs-th>
                </template>
                <template slot-scope="{data}">
                  <vs-tr :key="tr.id" v-for="tr in data">
                    <vs-td :data="tr.id">{{ tr.id }}</vs-td>
                    <vs-td :data="tr.name">{{ parseCateName(tr.name) }}</vs-td>
                    <vs-td :data="tr.id">
                      <vs-avatar size="70px" :src="tr.avatar" />
                    </vs-td>
                    <vs-td :data="tr.id">{{ tr.path }}</vs-td>
                    <vs-td :data="tr.id">
                      <router-link :to="{name:'edit_category',params:{id:tr.id}}">
                        <vs-button vs-type="gradient" size="lagre" color="success" icon="edit"></vs-button>
                      </router-link>
                      <vs-button vs-type="gradient" size="lagre" color="red" icon="delete_forever" @click="confirmDestroy(tr.id)"></vs-button>
                    </vs-td>
                  </vs-tr>
                </template>
              </vs-table>
            </div>
          </div>
        </div>
      </div>
      <vs-popup style="width:100%;" title="Thêm mới danh mục" :active.sync="popupActivo">
        <ModalAdd @closePopup="closePop($event)" />
      </vs-popup>
  </div>
</template>

<script>
import Sortable from "sortablejs";
import ModalAdd from "../../components/layouts/modal/category/add";
import { mapActions } from "vuex";

export default {
  data: () => ({
    keyword: null,
    popupActivo: false,
    list: [],
    timer: 0,
    id_item: "",
    sortableInstance: null,
    savingOrder: false
  }),
  components: {
    ModalAdd
  },
  computed: {
    hasKeyword() {
      return String(this.keyword || "").trim().length > 0;
    },
    canReorder() {
      return !this.hasKeyword && this.list.length > 1;
    }
  },
  watch: {
    canReorder(val) {
      if (val) {
        this.refreshSortable();
      } else {
        this.destroySortable();
      }
    }
  },
  methods: {
    ...mapActions(["listCate", "destroyCate", "reorderCate", "loadings"]),
    parseCateName(name) {
      try {
        return JSON.parse(name)[0].content;
      } catch (e) {
        return name;
      }
    },
    closePop(event) {
      this.listCategory();
      this.popupActivo = event;
    },
    listCategory() {
      this.loadings(true);
      this.listCate({ keyword: this.keyword })
        .then(response => {
          this.loadings(false);
          this.list = response.data;
          this.refreshSortable();
        });
    },
    searchCategory() {
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.listCate({ keyword: this.keyword })
          .then(response => {
            this.list = response.data;
            this.refreshSortable();
          });
      }, 800);
    },
    refreshSortable() {
      this.destroySortable();
      if (!this.canReorder) {
        return;
      }
      this.$nextTick(() => {
        this.$nextTick(() => {
          this.initSortable();
        });
      });
    },
    initSortable() {
      const tbody = this.$refs.sortableTbody;
      if (!tbody || this.sortableInstance) {
        return;
      }
      this.sortableInstance = Sortable.create(tbody, {
        animation: 180,
        handle: ".drag-handle",
        ghostClass: "cate-sortable-ghost",
        chosenClass: "cate-sortable-chosen",
        draggable: ".cate-sortable-row",
        delayOnTouchOnly: true,
        delay: 120,
        forceFallback: true,
        fallbackOnBody: true,
        onEnd: evt => {
          const { oldIndex, newIndex } = evt;
          if (oldIndex === newIndex || oldIndex == null || newIndex == null) {
            return;
          }
          const moved = this.list.splice(oldIndex, 1)[0];
          this.list.splice(newIndex, 0, moved);
          this.saveCategoryOrder();
        }
      });
    },
    destroySortable() {
      if (this.sortableInstance) {
        this.sortableInstance.destroy();
        this.sortableInstance = null;
      }
    },
    saveCategoryOrder() {
      if (this.savingOrder) {
        return;
      }
      this.savingOrder = true;
      const ids = this.list.map(item => item.id);
      this.reorderCate({ ids })
        .then(() => {
          this.$success("Đã cập nhật thứ tự danh mục");
        })
        .catch(() => {
          this.$error("Không thể lưu thứ tự danh mục");
          this.listCategory();
        })
        .finally(() => {
          this.savingOrder = false;
        });
    },
    destroy() {
      this.loadings(true);
      this.destroyCate({ id: this.id_item })
        .then(() => {
          this.listCategory();
          this.loadings(false);
          this.$success("Xóa danh mục thành công");
        });
    },
    confirmDestroy(id) {
      this.id_item = id;
      this.$vs.dialog({
        type: "confirm",
        color: "danger",
        title: "Bạn có chắc chắn",
        text: "Xóa danh mục này",
        accept: this.destroy
      });
    }
  },
  mounted() {
    this.listCategory();
  },
  beforeDestroy() {
    this.destroySortable();
  }
};
</script>

<style>
.cate-reorder-hint {
  display: inline-block;
  margin-left: 8px;
  font-size: 13px;
  color: #1f4f78;
  font-weight: 600;
}
.cate-reorder-hint--muted {
  color: #888;
  font-weight: 400;
}
.cate-sortable-table {
  margin-top: 12px;
  overflow-x: auto;
}
.cate-sortable-table__inner {
  margin-bottom: 0;
  background: #fff;
}
.cate-sortable-table__inner thead th {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
  border-bottom-width: 1px;
  white-space: nowrap;
}
.cate-sortable-table__inner tbody td {
  vertical-align: middle;
  font-size: 14px;
}
.cate-th-sort {
  width: 88px;
}
.cate-sortable-row {
  background: #fff;
}
.drag-handle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  margin-right: 6px;
  border-radius: 8px;
  color: #64748b;
  background: #f1f5f9;
  cursor: grab;
  vertical-align: middle;
  user-select: none;
  touch-action: none;
}
.drag-handle:hover {
  background: #e2e8f0;
  color: #1f4f78;
}
.drag-handle:active {
  cursor: grabbing;
}
.drag-handle .material-icons {
  font-size: 22px;
  line-height: 1;
}
.cate-sort-index {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 600;
}
.cate-sortable-ghost {
  opacity: 0.5;
  background: #eef4fa !important;
}
.cate-sortable-ghost td {
  background: #eef4fa !important;
}
.cate-sortable-chosen td {
  background: #f8fafc;
}
</style>
