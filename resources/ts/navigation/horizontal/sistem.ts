export default [
  {
    title: "Sistem Yönetimi",
    icon: { icon: "tabler-settings" },
    policyKey: "menu:sistem",
    children: [
      {
        title: "Kullanıcılar",
        icon: { icon: "tabler-users" },
        policyKey: "menu:sistem:kullanicilar",
        children: [
          {
            title: "Kullanıcı Listesi",
            to: "personel-kullanicilar",
            policyKey: "menu:sistem:kullanicilar:liste",
            target: "_blank",
          },
          {
            title: "Yetki Yönetimi",
            to: { path: "/personel/yetki-yonetimi" },
            icon: { icon: "tabler-shield-lock" },
            policyKey: "menu:sistem:kullanicilar:yetki-yonetimi",
            target: "_blank",
          },
          {
            title: "Kullanıcı Logları",
            to: { path: "/personel/loglar" },
            icon: { icon: "tabler-list-details" },
            policyKey: "menu:sistem:kullanicilar:loglar",
            target: "_blank",
          },
        ],
      },
      {
        title: "Geri Bildirimler",
        to: { path: "/sistem/feedback" },
        icon: { icon: "tabler-message-dots" },
        policyKey: "menu:sistem:feedback",
        target: "_blank",
      },
      {
        title: "Bildirim Ayarları",
        to: { path: "/sistem/feedback/ayarlar" },
        icon: { icon: "tabler-bell" },
        policyKey: "menu:sistem:feedback:ayarlar",
        target: "_blank",
      },
      {
        title: "ACL Policy Yönetimi",
        to: { path: "/sistem/acl-policy-yonetimi" },
        icon: { icon: "tabler-lock-cog" },
        policyKey: "menu:sistem:acl-policy-yonetimi",
        target: "_self",
      },
      {
        title: "Dosya Yönetimi",
        to: { path: "/sistem/dosya-yonetimi" },
        icon: { icon: "tabler-folder-open" },
        policyKey: "menu:sistem:dosya-yonetimi",
        target: "_self",
      },
    ],
  },
];
