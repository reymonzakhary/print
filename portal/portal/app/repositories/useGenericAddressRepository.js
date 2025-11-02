export const useGenericAddressRepository = (entityType) => {
  const api = useAPI();

  /**
   * Here I kept the same format as the address data from the API
   * but I removed the unnecessary fields and added some new fields
   * This is different from the approach seen in the invoiceRepository
   * where I mapped every single field.
   *
   * @param data - The address data to transform
   * @returns The transformed address data
   */
  const transformAddressData = (data) => {
    const transformedData = { ...data };
    if (data.number) transformedData.number = data.number ? data.number.trim() : null;
    if (data.company_name && data.company_name === "No Company")
      transformedData.is_business_user = false;
    if (data.company_name)
      transformedData.company_name = data.company_name === "No Company" ? null : data.company_name;
    if (data.tax_nr) transformedData.tax_nr = data.tax_nr === "000000000" ? null : data.tax_nr;
    if (data.phone_number)
      transformedData.phone_number = data.phone_number === "000000000" ? null : data.phone_number;
    if (data.address && transformedData.number)
      transformedData.full_address = `${data.address} ${transformedData.number}`;
    return transformedData;
  };

  const getAddresses = async (entityId) => {
    const addresses = await api.get(`/${entityType}/${entityId}/addresses`);
    const sanitized = addresses.data.map((address) => transformAddressData(address));
    return sanitized;
  };

  const deleteAddress = async (entityId, addressId) => {
    return await api.delete(`/${entityType}/${entityId}/addresses/${addressId}`);
  };

  const createAddress = async (entityId, address) => {
    const newAddress = { ...address };
    if (!address.is_business_user) {
      newAddress.company_name = "No Company";
      newAddress.tax_nr = "000000000";
      newAddress.phone_number = "000000000";
    } else if (address.company_name && address.company_name.length === 0) {
      newAddress.company_name = "No Company";
      newAddress.tax_nr = "000000000";
      newAddress.phone_number = "000000000";
    }
    const response = await api.post(`/${entityType}/${entityId}/addresses`, newAddress);
    return transformAddressData(response.data);
  };

  const updateAddress = async (entityId, address) => {
    if (address.id === undefined) throw new Error("Address ID is required to update an address");
    const newAddress = { ...address };
    if (!address.is_business_user) {
      newAddress.company_name = "No Company";
      newAddress.tax_nr = "000000000";
      newAddress.phone_number = "000000000";
    } else if (address.company_name && address.company_name.length === 0) {
      newAddress.company_name = "No Company";
      newAddress.tax_nr = "000000000";
      newAddress.phone_number = "000000000";
    }

    const response = await api.put(
      `/${entityType}/${entityId}/addresses/${address.id}`,
      newAddress,
    );
    return transformAddressData(response.data);
  };

  return { getAddresses, deleteAddress, createAddress, updateAddress, transformAddressData };
};
