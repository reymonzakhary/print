<template>
  <div class="container p-4">
    <UITable
      :title="{
        text: 'Books',
      }"
      :loading="loading"
      :data="books"
      :columns="columns"
      :columns-order="columnsOrder"
      :columns-visibility="columnsVisibility"
      :row-selection="rowsSelected"
      :options="{}"
      hover
      bordered
      :may-order-columns="true"
      :may-select-rows="true"
      :get-row-class="rowClassAdapter"
      @row-click="handleRowClick"
      @row-selection-change="rowsSelected = $event"
    />
    <UIButton class="mt-4" @click="addDemoRowToBooks"> Add Demo Row </UIButton>
    <UIButton class="mt-4" @click="removeDemoRowFromBooks"> Remove Demo Row </UIButton>
  </div>
</template>

<script setup>
const loading = ref(true);
const books = ref([]);

onMounted(() => {
  setTimeout(() => {
    books.value = [
      {
        title: "The Great Gatsby",
        author: "F. Scott Fitzgerald",
        publishYear: 1925,
        children: [
          { title: "Hamlet", author: "William Shakespeare", publishYear: 1603 },
          { title: "The Catcher in the Rye", author: "J.D. Salinger", publishYear: 1951 },
          { title: "The Hobbit", author: "J.R.R. Tolkien", publishYear: 1937 },
          { title: "Fahrenheit 451", author: "Ray Bradbury", publishYear: 1953 },
          { title: "Jane Eyre", author: "Charlotte Brontë", publishYear: 1847 },
          { title: "Brave New World", author: "Aldous Huxley", publishYear: 1932 },
          { title: "The Lord of the Rings", author: "J.R.R. Tolkien", publishYear: 1954 },
          {
            title: "The Chronicles of Narnia",
            author: "C.S. Lewis",
            publishYear: 1950,
            children: [
              { title: "Demo Book 1", author: "Author 1", publishYear: 2001 },
              { title: "Demo Book 2", author: "Author 2", publishYear: 2002 },
              { title: "Demo Book 3", author: "Author 3", publishYear: 2003 },
              { title: "Demo Book 4", author: "Author 4", publishYear: 2004 },
              { title: "Demo Book 5", author: "Author 5", publishYear: 2005 },
              { title: "Demo Book 6", author: "Author 6", publishYear: 2006 },
              { title: "Demo Book 7", author: "Author 7", publishYear: 2007 },
              { title: "Demo Book 8", author: "Author 8", publishYear: 2008 },
              { title: "Demo Book 9", author: "Author 9", publishYear: 2009 },
              { title: "Demo Book 10", author: "Author 10", publishYear: 2010 },
            ],
          },
          { title: "Animal Farm", author: "George Orwell", publishYear: 1945 },
          { title: "The Catcher in the Rye", author: "J.D. Salinger", publishYear: 1951 },
          { title: "The Grapes of Wrath", author: "John Steinbeck", publishYear: 1939 },
          { title: "Gone with the Wind", author: "Margaret Mitchell", publishYear: 1936 },
          { title: "The Picture of Dorian Gray", author: "Oscar Wilde", publishYear: 1890 },
          { title: "The Brothers Karamazov", author: "Fyodor Dostoevsky", publishYear: 1880 },
          { title: "Crime and Punishment", author: "Fyodor Dostoevsky", publishYear: 1866 },
          { title: "Wuthering Heights", author: "Emily Brontë", publishYear: 1847 },
          { title: "Les Misérables", author: "Victor Hugo", publishYear: 1862 },
          { title: "The Count of Monte Cristo", author: "Alexandre Dumas", publishYear: 1844 },
          { title: "Don Quixote", author: "Miguel de Cervantes", publishYear: 1605 },
          {
            title: "One Hundred Years of Solitude",
            author: "Gabriel Garcia Marquez",
            publishYear: 1967,
          },
          { title: "The Sound and the Fury", author: "William Faulkner", publishYear: 1929 },
          { title: "The Sun Also Rises", author: "Ernest Hemingway", publishYear: 1926 },
          { title: "Slaughterhouse-Five", author: "Kurt Vonnegut", publishYear: 1969 },
          { title: "Catch-22", author: "Joseph Heller", publishYear: 1961 },
          { title: "Beloved", author: "Toni Morrison", publishYear: 1987 },
          { title: "The Stranger", author: "Albert Camus", publishYear: 1942 },
        ],
      },
      { title: "Pride and Prejudice", author: "Jane Austen", publishYear: 1813 },
      { title: "To Kill a Mockingbird", author: "Harper Lee", publishYear: 1960 },
      { title: "1984", author: "George Orwell", publishYear: 1949 },
      { title: "Moby Dick", author: "Herman Melville", publishYear: 1851 },
      { title: "War and Peace", author: "Leo Tolstoy", publishYear: 1869 },
      { title: "The Odyssey", author: "Homer", publishYear: -800 },
      { title: "Ulysses", author: "James Joyce", publishYear: 1922 },
      { title: "The Divine Comedy", author: "Dante Alighieri", publishYear: 1320 },
      { title: "The Iliad", author: "Homer", publishYear: -750 },
      { title: "Fahrenheit 451", author: "Ray Bradbury", publishYear: 1953 },
      { title: "Jane Eyre", author: "Charlotte Brontë", publishYear: 1847 },
      { title: "Brave New World", author: "Aldous Huxley", publishYear: 1932 },
      { title: "The Iliad", author: "Homer", publishYear: -750 },
    ];
    loading.value = false;
  }, 1000);
});

const columnsOrder = ref(["title", "author", "publishYear"]);
const columnsVisibility = ref({
  title: true,
  author: true,
  publishYear: true,
});
const rowsSelected = ref({});

const columns = ref([
  {
    header: "Title",
    accessorKey: "title",
    enableHiding: false,
    meta: {
      class: "min-w-[250px]",
    },
  },
  {
    header: "Author",
    accessorKey: "author",
    meta: {
      class: "w-2/6 min-w-[250px] text-right",
    },
  },
  {
    header: "Publish Year",
    accessorKey: "publishYear",
    meta: {
      class: "w-1/6 min-w-[100px] text-right",
    },
  },
]);

function rowClassAdapter(table, row) {
  const publishYearColumn = table.getColumn("publishYear");
  if (!publishYearColumn) return "";

  const value = row.getValue(publishYearColumn.id);
  // if (value < 1950) {
  //   return "!bg-red-100 hover:!bg-red-200";
  // } else if (value < 2000) {
  //   return "!bg-yellow-100 hover:!bg-yellow-200";
  // } else {
  //   return "!bg-green-100 hover:!bg-green-200";
  // }
}

function addDemoRowToBooks() {
  books.value = [
    ...books.value,
    {
      title: "The Catcher in the Rye",
      author: "J.D. Salinger",
      publishYear: 1951,
    },
  ];
}

function removeDemoRowFromBooks() {
  books.value = books.value.slice(0, -1);
}

const handleRowClick = (row) => {
};
</script>
