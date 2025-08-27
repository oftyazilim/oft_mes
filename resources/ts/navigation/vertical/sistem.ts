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
            // target: "_blank",
          },
          {
            title: "Yetki Yönetimi",
            to: { path: "/personel/yetki-yonetimi" },
            icon: { icon: "tabler-shield-lock" },
            action: "manage",
            subject: "users",
          },
          {
            title: "Kullanıcı Logları",
            to: { path: "/personel/loglar" },
            icon: { icon: "tabler-list-details" },
            action: "manage",
            subject: "all",
          },
        ],
      },
    ],
  },
];
