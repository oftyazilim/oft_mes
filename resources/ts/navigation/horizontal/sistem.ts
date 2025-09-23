export default [
  {
    title: "Sistem Yönetimi",
    icon: { icon: "tabler-settings" },
    children: [
      {
        title: "Kullanıcılar",
        icon: { icon: "tabler-users" },
        children: [
          {
            title: "Kullanıcı Listesi",
            to: "personel-kullanicilar",
            action: "manage",
            subject: "all",
            target: "_blank",
          },
          {
            title: "Yetki Yönetimi",
            to: { path: "/personel/yetki-yonetimi" },
            icon: { icon: "tabler-shield-lock" },
            action: "manage",
            subject: "users",
            target: "_blank",
          },
          {
            title: "Kullanıcı Logları",
            to: { path: "/personel/loglar" },
            icon: { icon: "tabler-list-details" },
            action: "manage",
            subject: "all",
            target: "_blank",
          },
        ],
      },
      {
        title: "Geri Bildirimler",
        to: { path: "/sistem/feedback" },
        icon: { icon: "tabler-message-dots" },
        action: "manage",
        subject: "all",
      },
      {
        title: "Bildirim Ayarları",
        to: { path: "/sistem/feedback/ayarlar" },
        icon: { icon: "tabler-bell" },
        action: "manage",
        subject: "all",
      },
    ],
  },
];
