export const useCompanyRepository = () => {
  const get = async () => {
    return customers.value;
  };

  const getById = async (id) => {
    return business.value;
  };

  const getEmployees = async (id) => {
    return employees.value;
  };

  const getTeams = async (id) => {
    return teams.value;
  };

  const getAddresses = async (id) => {
    return addresses.value;
  };

  const business = ref({
    id: 1,
    name: "Prindustry",
    logo: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTJ9uo29rKi1lXepifVFHiXtetcFLN7dyZhcQ&s",
    country: "Netherlands",
    city: "Breda",
    address: "Liniestraat 17",
    postalCode: "4816 BG",
    website: "https://prindustry.com",
    kvk: "12345678",
    btw: "NL123456789B01",
    contact: {
      name: "Ramon van Wingerden",
      email: "ramonvanwingerden@prindsutry.com",
      phone: "+31 6 12345678",
    },
  });

  const addresses = ref([
    {
      id: 5,
      team_address: false,
      team_id: null,
      team_name: "administrator",
      address: "Aubachstraat",
      number: "321       ",
      city: "Beverwijk",
      region: "Noord-Holland",
      zip_code: "1946XJ",
      default: false,
      type: "work",
      full_name: "Melvin Idema",
      company_name: "No Company",
      phone_number: "000000000",
      tax_nr: "000000000",
      lat: null,
      lng: null,
      created_at: "2024-07-23T10:55:32.000000Z",
      updated_at: "2024-07-23T10:55:32.000000Z",
    },
    {
      id: 6,
      team_address: true,
      team_id: 3,
      team_name: "Marketing",
      address: "Aubachstraat",
      number: "000       ",
      city: "Beverwijk",
      region: "Noord-Holland",
      zip_code: "1946XJ",
      default: false,
      type: "home",
      full_name: "Melvin Idema",
      company_name: "No Company",
      phone_number: "000000000",
      tax_nr: "000000000",
      lat: null,
      lng: null,
      created_at: "2024-07-23T14:48:18.000000Z",
      updated_at: "2024-07-23T14:48:18.000000Z",
    },
  ]);

  const teams = ref([
    {
      id: 1,
      name: "Sales",
      members: 5,
    },
    {
      id: 2,
      name: "Development",
      members: 10,
    },
    {
      id: 3,
      name: "Marketing",
      members: 3,
    },
    {
      id: 4,
      name: "Support",
      members: 7,
    },
    {
      id: 5,
      name: "Management",
      members: 2,
    },
  ]);

  const employees = ref([
    {
      id: 1,
      name: "Ramon van Wingerden",
      email: "ramonvanwingerden@prindustry.com",
      phone: "+31 6 12345678",
      teams: ["sales"],
    },
    {
      id: 2,
      name: "Jeroen van Wingerden",
      email: "jeroen@prindustry.com",
      phone: "+31 6 12345678",
      teams: ["sales", "development"],
    },
    {
      id: 3,
      name: "Johanna Loem",
      email: "johanna@prindustry.com",
      phone: "+31 6 12345678",
      teams: ["sales", "marketing"],
    },
  ]);

  const customers = ref([
    {
      type: "business",
      id: 1,
      logo: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTJ9uo29rKi1lXepifVFHiXtetcFLN7dyZhcQ&s",
      name: "Prindustry B.V.",
      email: "ramon@prindustry.com",
      phone: "+31640148611",
      orders: 621,
      revenue: 120091873,
    },
    {
      type: "business",
      id: 3,
      logo: "https://upload.wikimedia.org/wikipedia/commons/f/f8/Volt_Logo_2021.png",
      name: "Volt",
      email: "info@volt.eu",
      phone: "+31640148611",
      orders: 621,
      revenue: 12091873,
    },
    {
      type: "business",
      id: 4,
      logo: "https://example.com/techsolutions.png",
      name: "TechSolutions Inc.",
      email: "info@techsolutions.com",
      phone: "+14155552671",
      orders: 892,
      revenue: 45678901,
    },
    {
      type: "business",
      id: 6,
      logo: "https://example.com/greenenergy.png",
      name: "GreenEnergy Co.",
      email: "contact@greenenergy.com",
      phone: "+4930901820",
      orders: 1205,
      revenue: 78901234,
    },
    {
      type: "business",
      id: 8,
      logo: "https://example.com/fashionforward.png",
      name: "FashionForward Ltd.",
      email: "sales@fashionforward.co.uk",
      phone: "+442071234567",
      orders: 3781,
      revenue: 23456789,
    },
    {
      type: "business",
      id: 10,
      logo: "https://example.com/nordicconstructions.png",
      name: "Nordic Constructions AB",
      email: "info@nordicconstructions.se",
      phone: "+46812345678",
      orders: 467,
      revenue: 89012345,
    },
    {
      type: "business",
      id: 12,
      logo: "https://example.com/globallogistics.png",
      name: "Global Logistics Corp.",
      email: "support@globallogistics.com",
      phone: "+16467890123",
      orders: 2956,
      revenue: 234567890,
    },
    {
      type: "business",
      id: 14,
      logo: "https://example.com/australianmining.png",
      name: "Australian Mining Enterprises",
      email: "info@ausmining.com.au",
      phone: "+61234567890",
      orders: 831,
      revenue: 345678901,
    },
    {
      type: "business",
      id: 16,
      logo: "https://example.com/canadianfoods.png",
      name: "Canadian Foods Inc.",
      email: "contact@canadianfoods.ca",
      phone: "+14169876543",
      orders: 1567,
      revenue: 56789012,
    },
    {
      type: "business",
      id: 18,
      logo: "https://example.com/braziliantextiles.png",
      name: "Brazilian Textiles Ltd.",
      email: "info@braziliantextiles.com.br",
      phone: "+551123456789",
      orders: 2103,
      revenue: 67890123,
    },
    {
      type: "business",
      id: 20,
      logo: "https://example.com/russiansteel.png",
      name: "Russian Steel Works",
      email: "contact@russiansteel.ru",
      phone: "+74951234567",
      orders: 729,
      revenue: 78901234,
    },
    {
      type: "business",
      id: 22,
      logo: "https://example.com/indiansoftware.png",
      name: "Indian Software Solutions",
      email: "support@indiansoftware.in",
      phone: "+919876543210",
      orders: 3452,
      revenue: 89012345,
    },
    {
      type: "business",
      id: 24,
      logo: "https://example.com/mexicanfoodco.png",
      name: "Mexican Food Co.",
      email: "info@mexicanfood.mx",
      phone: "+525512345678",
      orders: 1876,
      revenue: 90123456,
    },
  ]);

  return {
    customers,
    get,
    getById,
    getEmployees,
    getTeams,
    getAddresses,
  };
};
